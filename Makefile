# === Makefile Helper ===

# Styles
YELLOW=$(shell echo "\033[00;33m")
RED=$(shell echo "\033[00;31m")
RESTORE=$(shell echo "\033[0m")

# Variables
PHP := php
SYMFONY := symfony
COMPOSER := composer
CURRENT_DIR := $(shell pwd)
.DEFAULT_GOAL := list
EZ_DIR := $(CURRENT_DIR)/ezplatform
PUMLJAR := documentation/bin/plantuml.jar

.PHONY: list
list:
	@echo "******************************"
	@echo "${YELLOW}Available targets${RESTORE}:"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf " ${YELLOW}%-15s${RESTORE} > %s\n", $$1, $$2}'
	@echo "${RED}==============================${RESTORE}"

.PHONY: installez
installez: intall ## Install eZ as the local project
	@docker run -d -p 3366:3306 --name ezdbnovaezsafactorycontainer -e MYSQL_ROOT_PASSWORD=ezplatform mariadb:10.2
	@composer create-project ezsystems/ezplatform-ee --prefer-dist --no-progress --no-interaction --no-scripts $(EZ_DIR)
	@curl -o tests/provisioning/wrap.php https://raw.githubusercontent.com/Plopix/symfony-bundle-app-wrapper/master/wrap-bundle.php
	@WRAP_APP_DIR=./ezplatform WRAP_BUNDLE_DIR=./ php tests/provisioning/wrap.php
	@rm tests/provisioning/wrap.php
	@echo "DATABASE_URL=mysql://root:ezplatform@127.0.0.1:3366/ezplatform" >>  $(EZ_DIR)/.env.local
	@cd $(EZ_DIR) && COMPOSER_MEMORY_LIMIT=-1 composer update
	@cd $(EZ_DIR) && $(COMPOSER) ezplatform-install
	@cd $(EZ_DIR) && $(CONSOLE) cache:clear

.PHONY: serveez
serveez: stopez ## Clear the cache and start the web server
	@cd $(EZ_DIR) && rm -rf var/cache/*
	@docker start ezdbnovaezsafactorycontainer
	@cd $(EZ_DIR) && bin/console cache:clear
	@cd $(EZ_DIR) && $(SYMFONY) local:server:start -d


.PHONY: stopez
stopez: ## Stop the web server if it is running
	@-cd $(EZ_DIR) && $(SYMFONY) local:server:stop
	@-docker stop ezdbnovaezsafactorycontainer

.PHONY: codeclean
codeclean: ## Coding Standard checks
	$(PHP) ./vendor/bin/php-cs-fixer fix --config=.cs/.php_cs.php
	$(PHP) ./vendor/bin/phpcs --standard=.cs/cs_ruleset.xml --extensions=php bundle tests
	$(PHP) ./vendor/bin/phpmd bundle,tests text .cs/md_ruleset.xml

.PHONY: tests
tests: ## Run the tests
	$(PHP) ./vendor/bin/phpunit ./tests --exclude-group behat

.PHONY: install
install: ## Install vendors
	$(COMPOSER) install


.PHONY: resetdb
resetdb:
	@cd $(EZ_DIR) && bin/console doctrine:database:drop --force
	@cd $(EZ_DIR) && bin/console ezplatform:install clean
	@cd $(EZ_DIR) && bin/console novaezsiteaccessfactory:install --siteaccess=admin

.PHONY: clean
clean: stopez ## Removes the vendors, and caches
	@-rm -f .php_cs.cache
	@-rm -rf vendor
	@-rm -rf ezplatform
	@-rm  node_modules
	@-docker rm ezdbnovaezsafactorycontainer

.PHONY: docs
docs: ## Generate the documentation files and images
	@echo "Generation..."
#	@java -jar $(PUMLJAR) -o export documentation/puml/*.puml
#	@java -jar $(PUMLJAR) -o export -tsvg documentation/puml/*.puml
	@$(EZ_DIR)/bin/console workflow:dump site_configuration > documentation/dot/site_configuration.dot
	@cat documentation/dot/site_configuration.dot | java -jar $(PUMLJAR) -p > documentation/export/site_configuration.png
	@cat documentation/dot/site_configuration.dot | java -jar $(PUMLJAR) -p -tsvg > documentation/export/site_configuration.svg
	@echo "=>> [OK]"

