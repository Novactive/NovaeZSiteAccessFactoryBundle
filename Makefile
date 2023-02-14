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
IBEXA_DIR := $(CURRENT_DIR)/ibexa
PUMLJAR := documentation/bin/plantuml.jar

.PHONY: list
list:
	@echo "******************************"
	@echo "${YELLOW}Available targets${RESTORE}:"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf " ${YELLOW}%-15s${RESTORE} > %s\n", $$1, $$2}'
	@echo "${RED}==============================${RESTORE}"

.PHONY: install-ibexa
install-ibexa: ## Install Ibexa as the local project
	#@docker run -p 3366:3306 --name ezdbnovaezsafactorycontainer -e MYSQL_ROOT_PASSWORD=ibexa mariadb:10.2
	@composer create-project ibexa/commerce-skeleton="4.2.0" --prefer-dist --no-progress --no-scripts $(IBEXA_DIR)
	@echo "Please set up this way:"
	@echo "\tenv(DATABASE_HOST)     -> 127.0.0.1"
	@echo "\tenv(DATABASE_PORT)     -> 3366"
	@echo "\tenv(DATABASE_PASSWORD) -> ibexa"
	@cd $(IBEXA_DIR) && COMPOSER_MEMORY_LIMIT=-1 composer update --lock
	@cd $(IBEXA_DIR) && bin/console ibexa:install clean
	@cd $(IBEXA_DIR) && bin/console cache:clear

.PHONY: serve-ibexa
serve-ibexa: stop-ibexa ## Clear the cache and start the web server
	@cd $(IBEXA_DIR) && rm -rf var/cache/*
	@docker start ezdbnovaezsafactorycontainer
	@cd $(IBEXA_DIR) && bin/console cache:clear
	@cd $(IBEXA_DIR) && $(SYMFONY) local:server:start -d


.PHONY: stop-ibexa
stop-ibexa: ## Stop the web server if it is running
	@cd $(IBEXA_DIR) && $(SYMFONY) local:server:stop
	@docker stop ezdbnovaezsafactorycontainer

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
	@cd $(IBEXA_DIR) && bin/console doctrine:database:drop --force
	@cd $(IBEXA_DIR) && bin/console ibexa:install clean
	@cd $(IBEXA_DIR) && bin/console novaezsiteaccessfactory:install --siteaccess=admin

.PHONY: clean
clean: ## Removes the vendors, and caches
	rm -f .php_cs.cache
	rm -rf vendor
	rm -f composer.lock

.PHONY: docs
docs: ## Generate the documentation files and images
	@echo "Generation..."
#	@java -jar $(PUMLJAR) -o export documentation/puml/*.puml
#	@java -jar $(PUMLJAR) -o export -tsvg documentation/puml/*.puml
	@$(IBEXA_DIR)/bin/console workflow:dump site_configuration > documentation/dot/site_configuration.dot
	@cat documentation/dot/site_configuration.dot | java -jar $(PUMLJAR) -p > documentation/export/site_configuration.png
	@cat documentation/dot/site_configuration.dot | java -jar $(PUMLJAR) -p -tsvg > documentation/export/site_configuration.svg
	@echo "=>> [OK]"

