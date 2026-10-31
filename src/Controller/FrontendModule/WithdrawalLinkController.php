<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Routing\DeclarationLinkGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The first step of the process. Meant for the footer of every page.
 */
#[AsFrontendModule('withdrawal_link', category: 'miscellaneous')]
class WithdrawalLinkController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly DeclarationLinkGenerator $links,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $type = DeclarationType::tryFrom((string) $model->rowFunction) ?? DeclarationType::Withdrawal;
        $url = $this->links->generate($type, $this->getPageModel());

        if (null === $url) {
            return new Response();
        }

        $template->set('url', $url);
        $template->set('label', $this->translator->trans($type->entryLabelKey(), [], DeclarationType::TRANSLATION_DOMAIN));
        $template->set('function', $type->value);

        return $template->getResponse();
    }
}
