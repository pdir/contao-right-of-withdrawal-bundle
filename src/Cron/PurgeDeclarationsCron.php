<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationPurger;

#[AsCronJob('daily')]
class PurgeDeclarationsCron
{
    public function __construct(private readonly DeclarationPurger $purger)
    {
    }

    public function __invoke(): void
    {
        $this->purger->purge(new \DateTimeImmutable());
    }
}
