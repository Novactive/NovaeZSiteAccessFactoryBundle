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
use Doctrine\Persistence\ManagerRegistry;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Controller\Controller;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Service\SiteConfiguration as SiteConfigurationService;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Form\Admin\SiteConfigurationType;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Repository\SiteConfiguration as SiteConfigurationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\Registry;

/**
 * @Route("/site/configuration")
 */
class SiteConfigurationController extends Controller
{
    /**
     * @var array
     */
    private $siteAccessDefaultGroupList;

    public function __construct(array $siteAccessDefaultGroupList = [])
    {
        $this->siteAccessDefaultGroupList = $siteAccessDefaultGroupList;
    }

    /**
     * @Route("/edit/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_edit")
     * @Route("/create", name="novaezsiteaccessfactoryadmin_siteconfiguration_create")
     * @Template("@ibexadesign/site_configuration/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function edit(
        Request $request,
        ManagerRegistry $registry,
        Registry $workflows,
        ?int $id = null
    ) {
        $entityManager = $registry->getManager();
        if (null === $id) {
            $siteConfiguration = new SiteConfiguration();
            $siteConfiguration->setType('standard');
            $siteConfiguration->setTheme('standard');
            $siteConfiguration->setPrioritizedLanguges(['eng-GB']);
            $siteConfiguration->setCreated(new DateTime());
            $siteConfiguration->setGroups($this->siteAccessDefaultGroupList);
        } else {
            $siteConfiguration = $this->retrieveOrNotFoundSiteConfiguration($entityManager, $id);
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
        int $id,
        Registry $workflows,
        ManagerRegistry $registry
    ): RedirectResponse {
        $entityManager = $registry->getManager();
        $siteConfiguration = $this->retrieveOrNotFoundSiteConfiguration($entityManager, $id);
        $workflow = $workflows->get($siteConfiguration);
        if (false === $workflow->can($siteConfiguration, $transition)) {
            throw new AccessDeniedException('Not authorized to process this transition.');
        }
        $workflow->apply($siteConfiguration, $transition);

        $entityManager->persist($siteConfiguration);
        $entityManager->flush();

        return new RedirectResponse(
            $this->router->generate('novaezsiteaccessfactoryadmin_siteconfiguration_index')
        );
    }

    /**
     * @Route("/duplicate/{id}", name="novaezsiteaccessfactoryadmin_siteconfiguration_duplicate")
     */
    public function duplicate(
        int $id,
        ManagerRegistry $registry,
        SiteConfigurationService $service
    ): RedirectResponse {
        $entityManager = $registry->getManager();
        $siteConfiguration = $this->retrieveOrNotFoundSiteConfiguration($entityManager, $id);
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
        int $id,
        ManagerRegistry $registry,
        SiteConfigurationService $service
    ): RedirectResponse {
        $entityManager = $registry->getManager();
        $siteConfiguration = $this->retrieveOrNotFoundSiteConfiguration($entityManager, $id);
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
     * @Template("@ibexadesign/site_configuration/index.html.twig")
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

    /**
     * We need that function as we don't want to use the Generic Entity Manager
     * Used by default when using Param Converter.
     */
    private function retrieveOrNotFoundSiteConfiguration(
        EntityManagerInterface $entityManager,
        int $id
    ): SiteConfiguration {
        $siteConfiguration = $entityManager->getRepository(SiteConfiguration::class)->findOneById($id);
        if (!$siteConfiguration instanceof SiteConfiguration) {
            throw new NotFoundHttpException();
        }

        return $siteConfiguration;
    }
}
