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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Validator\Constraints;

use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LanguageValidator extends ConstraintValidator
{
    use EzRepositoryAware;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint): void
    {
        $languages = $this->repository->getContentLanguageService()->loadLanguages();
        $existingLanguages = [];
        foreach ($languages as $language) {
            $existingLanguages[] = $language->languageCode;
        }

        $result = array_diff($value, $existingLanguages);

        if (\count($result) > 0) {
            $this->context->buildViolation(
                $this->translator->trans(
                    $constraint->message,
                    ['%missing%' => implode(',', $result)],
                    'novaezsiteaccessfactory'
                )
            )->addViolation();
        }
    }
}
