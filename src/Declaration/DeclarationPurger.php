<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

class DeclarationPurger
{
    private const IP_HASH_RETENTION = 90 * 86400;

    public function __construct(
        private readonly DeclarationRepository $repository,
        private readonly SiteSettingsProvider $settings,
    ) {
    }

    /**
     * The retention period ends with the calendar year: a declaration from 2026
     * with three years of retention is kept until the end of 2029.
     */
    public function purge(\DateTimeImmutable $now): int
    {
        $deleted = 0;

        foreach ($this->repository->findRootPages() as $rootPage) {
            $years = $this->settings->get($rootPage)->retentionYears;
            $cutoff = $now->setDate((int) $now->format('Y') - $years, 1, 1)->setTime(0, 0);

            $deleted += $this->repository->deleteOlderThan($rootPage, $cutoff->getTimestamp());
        }

        $this->repository->clearIpHashesOlderThan($now->getTimestamp() - self::IP_HASH_RETENTION);

        return $deleted;
    }
}
