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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Validator\Constraints as eZSiteAccessFactoryAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="novaez_siteaccess_factory_site_configuration")
 * @ORM\Entity(repositoryClass="Novactive\Bundle\eZSiteAccessFactoryBundle\Repository\SiteConfiguration")
 * @ORM\EntityListeners({
 *     "Novactive\Bundle\eZSiteAccessFactoryBundle\Listener\EzUserLink",
 *     "Novactive\Bundle\eZSiteAccessFactoryBundle\Listener\EzRootLocationLink"
 * })
 * @UniqueEntity("siteaccessName")
 */
class SiteConfiguration implements Compose\UserLinkedInterface, Compose\RootLocationLinkedInterface
{
    use Compose\Metadata;
    use Compose\Status;
    use Compose\UserLinked;
    use Compose\RootLocationLinked;

    /**
     * When that is just a record in the database.
     */
    public const STATUS_DRAFT = 'draft';

    /**
     * Has been activated then it is pending to be processed.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * State when the process in progress.
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * Content Tree and Permissions are here but not for Anonymous.
     */
    public const STATUS_AVAILABLE = 'available';

    /**
     * Has been activated, that is ready to be open to the world, waitng for enable process.
     */
    public const STATUS_READY = 'ready';

    /**
     * State when the process is in progress.
     */
    public const STATUS_ENABLING = 'enabling';

    /**
     * Permissions on Anonymous have been done, SiteAccess is alive.
     */
    public const STATUS_ENABLED = 'enabled';

    /**
     * Waiting to be suspended.
     */
    public const STATUS_UNSUITABLE = 'unsuitable';

    /**
     * Start of the process to suspend.
     */
    public const STATUS_SUSPENDING = 'suspending';

    /**
     * Permissions on Anonymous have been remove, SiteAccess is NOT alive anymore.
     */
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Error Status.
     */
    public const STATUS_ERROR = 'errored';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_AVAILABLE,
        self::STATUS_READY,
        self::STATUS_ENABLING,
        self::STATUS_ENABLED,
        self::STATUS_ERROR,
        self::STATUS_UNSUITABLE,
        self::STATUS_SUSPENDING,
        self::STATUS_SUSPENDED,
    ];

    public const STATUS_STYLES = [
        self::STATUS_DRAFT => 'secondary',
        self::STATUS_PENDING => 'primary',
        self::STATUS_PROCESSING => 'dark',
        self::STATUS_AVAILABLE => 'success',
        self::STATUS_READY => 'info',
        self::STATUS_ENABLING => 'dark',
        self::STATUS_ENABLED => 'success',
        self::STATUS_ERROR => 'danger',
        self::STATUS_UNSUITABLE => 'warning',
        self::STATUS_SUSPENDING => 'warning',
        self::STATUS_SUSPENDED => 'warning',
    ];

    public const TRANS_EDIT = 'edit';
    public const TRANS_ACTIVATE = 'activate';
    public const TRANS_START_PROCESS = 'start_process';
    public const TRANS_PROCESS = 'process';

    public const TRANS_PUBLISH = 'publish';

    public const TRANS_START_ENABLE = 'start_enable';
    public const TRANS_ENABLE = 'enable';

    public const TRANS_UNPUBLISH = 'unpublish';

    public const TRANS_SUSPEND = 'suspend';
    public const TRANS_START_SUSPEND = 'start_suspend';

    public const TRANS_FAIL = 'fail';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="SC_id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_siteaccess_name", type="string",length=255, nullable=false, unique=true)
     * @Assert\NotBlank
     */
    private $siteaccessName;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_identifier", type="text", length=255, nullable=false)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="SC_prioritized_languages", type="json", nullable=false)
     * @eZSiteAccessFactoryAssert\Language
     */
    private $prioritizedLanguges;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_cache_pool", type="string", length=255, nullable=false)
     */
    private $cachePool;

    /**
     * @var array
     *
     * @ORM\Column(name="SC_groups", type="json", nullable=false)
     */
    private $groups;

    /**
     * @var array
     *
     * @ORM\Column(name="SC_page_builder_group", type="json", nullable=false)
     */
    private $pageBuilderGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_type", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_theme", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     */
    private $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="SC_admin_email", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $adminEmail;

    /**
     * @var
     *
     * @ORM\Column(name="SC_template", type="integer")
     */
    private $template;

    public function __construct()
    {
        $this->setLastStatus(self::STATUS_DRAFT);

        $this->cachePool = '%cache_pool%';
        $this->prioritizedLanguges = ['eng-GB'];
        $this->pageBuilderGroup = ['admin_group'];
        $this->groups = [];
    }

    public function getId(): ?int
    {
        return (int) $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSiteaccessName(): string
    {
        return $this->siteaccessName ?? '';
    }

    public function setSiteaccessName(?string $siteaccessName): void
    {
        $this->siteaccessName = $siteaccessName;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrioritizedLanguges(): array
    {
        return $this->prioritizedLanguges ?? [];
    }

    public function setPrioritizedLanguges(array $prioritizedLanguges): self
    {
        $this->prioritizedLanguges = $prioritizedLanguges;

        return $this;
    }

    public function getCachePool(): string
    {
        return $this->cachePool;
    }

    public function setCachePool(?string $cachePool): self
    {
        $this->cachePool = $cachePool;

        return $this;
    }

    public function getDesign(): string
    {
        return "{$this->getType()}_{$this->getTheme()}";
    }

    public function getType(): string
    {
        return $this->type ?? '';
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTheme(): string
    {
        return $this->theme ?? '';
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getStatusStyle(): string
    {
        return self::STATUS_STYLES[$this->lastStatus];
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    public function getPageBuilderGroup(): array
    {
        return $this->pageBuilderGroup;
    }

    public function setPageBuilderGroup(array $pageBuilderGroup): self
    {
        $this->pageBuilderGroup = $pageBuilderGroup;

        return $this;
    }

    public function getAdminEmail(): string
    {
        return $this->adminEmail ?? '';
    }

    public function setAdminEmail(?string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;

        return $this;
    }

    public function getTemplate(): int
    {
        return (int) $this->modele;
    }

    public function setTemplate(int $modele): self
    {
        $this->modele = $modele;

        return $this;
    }
}
