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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Controller\Admin;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Controller\Controller;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Service\SiteConfiguration as SiteConfigurationService;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Form\Admin\SiteConfigurationType;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Repository\SiteConfiguration as SiteConfigurationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\Registry;

/**
 * @Route("/site/configuration")
 */
class SiteConfigurationController extends Controller
{
    /**
     * @Route("/edit/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_edit")
     * @Route("/create", name="novaezsiteaccessfactoryadmin_siteconfiguration_create")
     * @Template("@ezdesign/site_configuration/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Registry $workflows,
        ?SiteConfiguration $siteConfiguration = null
    ) {
        if (null === $siteConfiguration) {
            $siteConfiguration = new SiteConfiguration();
            $siteConfiguration->setType('standard');
            $siteConfiguration->setTheme('standard');
            $siteConfiguration->setPrioritizedLanguges(['eng-GB']);
            $siteConfiguration->setCreated(new DateTime());
        }
        $workflow = $workflows->get($siteConfiguration);
        if (false === $workflow->can($siteConfiguration, 'edit')) {
            throw new AccessDeniedException('Not authorized to process this transition.');
        }

        $siteConfiguration->setUser($this->getUser());
        $form = $this->createForm(SiteConfigurationType::class, $siteConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siteConfiguration->setUpdated(new DateTime());
            $workflow->apply($siteConfiguration, 'edit');
            $entityManager->persist($siteConfiguration);
            $entityManager->flush();

            return new RedirectResponse(
                $this->router->generate('novaezsiteaccessfactoryadmin_siteconfiguration_index')
            );
        }

        return [
            'item' => $siteConfiguration,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/transit/{transition}/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_transit")
     */
    public function transit(
        string $transition,
        SiteConfiguration $siteConfiguration,
        Registry $workflows,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $workflow = $workflows->get($siteConfiguration);
        if (false === $workflow->can($siteConfiguration, $transition)) {
            throw new AccessDeniedException('Not authorized to process this transition.');
        }
        $workflow->apply($siteConfiguration, $transition);
        $entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('novaezsiteaccessfactoryadmin_siteconfiguration_index')
        );
    }

    /**
     * @Route("/duplicate/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_duplicate")
     */
    public function duplicate(
        SiteConfiguration $siteConfiguration,
        EntityManagerInterface $entityManager,
        SiteConfigurationService $service
    ): RedirectResponse {
        $new = $service->duplicate($siteConfiguration);
        $new->setUser($this->getUser());
        $entityManager->persist($new);
        $entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('novaezsiteaccessfactoryadmin_siteconfiguration_index')
        );
    }

    /**
     * @Route("/addtranslation/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_addtranslation")
     */
    public function addTranslation(
        SiteConfiguration $siteConfiguration,
        EntityManagerInterface $entityManager,
        SiteConfigurationService $service
    ): RedirectResponse {
        if ($siteConfiguration->getRootLocationId() > 0) {
            $new = $service->duplicate($siteConfiguration, 'translated');
            $new->setUser($this->getUser());
            $new->setRootLocationId($siteConfiguration->getRootLocationId());
            $entityManager->persist($new);
            $entityManager->flush();
        }

        return new RedirectResponse(
            $this->router->generate('novaezsiteaccessfactoryadmin_siteconfiguration_index')
        );
    }

    /**
     * @Route("/{status}/{page}/{limit}", name="novaezsiteaccessfactoryadmin_siteconfiguration_index",
     *                                    defaults={"page":1, "limit":10, "status":"all"})
     * @Template("@ezdesign/site_configuration/index.html.twig")
     */
    public function index(
        SiteConfigurationRepository $repository,
        int $page = 1,
        int $limit = 10,
        string $status = 'all'
    ): array {
        $filters = [
            'status' => 'all' === $status ? null : $status,
        ];

        return [
            'pager' => $repository->getPagerFilters($filters, $page, $limit),
            'statuses' => $repository->fetchStatusesData($filters),
            'currentStatus' => $status,
        ];
    }
}
