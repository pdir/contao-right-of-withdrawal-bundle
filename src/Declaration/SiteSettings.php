<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

/**
 * The settings of a website root.
 */
final class SiteSettings
{
    public const DEFAULT_RETENTION_YEARS = 3;

    public function __construct(
        public readonly int $rootPage,
        public readonly Jurisdiction $jurisdiction,
        public readonly int $withdrawalPage,
        public readonly int $cancellationPage,
        public readonly string $recipient,
        public readonly int $retentionYears,
        public readonly int $notification,
        public readonly string $language = 'de',
    ) {
    }

    public function targetPage(DeclarationType $type): int
    {
        if (!$this->jurisdiction->supports($type)) {
            return 0;
        }

        return DeclarationType::Withdrawal === $type ? $this->withdrawalPage : $this->cancellationPage;
    }

    public function isEnabled(DeclarationType $type): bool
    {
        return $this->targetPage($type) > 0;
    }
}
