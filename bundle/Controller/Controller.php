<?php

/**
 * Nova eZ SiteAccess Factory Bundle.
 *
 * @author    Sébastien Morel aka Plopix <morel.seb@gmail.com>
 * @author    Novactive <dir.tech@novactive.com>
 * @copyright 2019 Novactive
 * @license   https://github.com/Novactive/NovaeZSiteAccessFactoryBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Controller;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Security\User as SecurityUser;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Novactive\Bundle\eZExtraBundle\Core\Helper\eZ\Wrapper;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\WrapperFactoryAware;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

abstract class Controller
{
    use EzRepositoryAware;
    use WrapperFactoryAware;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var GlobalHelper
     */
    private $globalHelper;

    /**
     * @var ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @required
     */
    public function setDependencies(
        ConfigResolverInterface $configResolver,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        GlobalHelper $globalHelper,
        Environment $twig,
        TokenStorageInterface $tokenStorage
    ): self {
        $this->configResolver = $configResolver;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->globalHelper = $globalHelper;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     */
    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    protected function getRootLocation(): Location
    {
        return $this->globalHelper->getRootLocation();
    }

    protected function getSiteaccess(): ?SiteAccess
    {
        return $this->globalHelper->getSiteaccess();
    }

    protected function getPrioritizedLanguages(): array
    {
        return $this->configResolver->getParameter('languages');
    }

    protected function getCurrentLanguage(): string
    {
        return $this->getPrioritizedLanguages()[0];
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    public function getUser(): ?Wrapper
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }
        $eZUser = $token->getUser();
        if (!$eZUser instanceof SecurityUser) {
            return null;
        }

        return $this->wrapperFactory->createByContent($eZUser->getAPIUser());
    }
}
