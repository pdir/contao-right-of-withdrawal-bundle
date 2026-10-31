<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Routing;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Symfony\Component\Routing\Exception\ExceptionInterface;

class DeclarationLinkGenerator
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly SiteSettingsProvider $settings,
    ) {
    }

    /**
     * @return string|null The URL or null if the function is not available on this website
     */
    public function generate(DeclarationType $type, PageModel|null $currentPage): string|null
    {
        if (null === $currentPage) {
            return null;
        }

        $currentPage->loadDetails();

        $target = $this->settings->get((int) $currentPage->rootId)->targetPage($type);
        $page = $target > 0 ? $this->framework->getAdapter(PageModel::class)->findById($target) : null;

        if (null === $page) {
            return null;
        }

        try {
            return $this->urlGenerator->generate($page);
        } catch (ExceptionInterface) {
            return null;
        }
    }
}
