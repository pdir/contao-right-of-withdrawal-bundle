<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\PageModel;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Routing\DeclarationLinkGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * {{withdrawal_link}}, {{withdrawal_url}}, {{cancellation_link}}, {{cancellation_url}}
 */
#[AsInsertTag('withdrawal_link')]
#[AsInsertTag('withdrawal_url')]
#[AsInsertTag('cancellation_link')]
#[AsInsertTag('cancellation_url')]
class DeclarationLinkInsertTag
{
    public function __construct(
        private readonly DeclarationLinkGenerator $links,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(ResolvedInsertTag $tag): InsertTagResult
    {
        [$function, $output] = explode('_', $tag->getName());

        $type = DeclarationType::from($function);
        $page = $this->requestStack->getMainRequest()?->attributes->get('pageModel');
        $url = $this->links->generate($type, $page instanceof PageModel ? $page : null);

        if (null === $url) {
            return new InsertTagResult('');
        }

        if ('url' === $output) {
            return new InsertTagResult($url, OutputType::url);
        }

        return new InsertTagResult(
            \sprintf('<a href="%s" class="withdrawal-link withdrawal-link--%s"><strong>%s</strong></a>', htmlspecialchars($url), $type->value, $this->translator->trans($type->entryLabelKey(), [], DeclarationType::TRANSLATION_DOMAIN)),
            OutputType::html,
        );
    }
}
