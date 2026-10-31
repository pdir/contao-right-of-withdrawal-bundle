<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\ContaoManager;

use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Pdir\ContaoRightOfWithdrawalBundle\ContaoManager\Plugin;
use Pdir\ContaoRightOfWithdrawalBundle\PdirContaoRightOfWithdrawalBundle;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testRegistersBundle(): void
    {
        $bundles = (new Plugin())->getBundles(new DelegatingParser());

        $this->assertSame(PdirContaoRightOfWithdrawalBundle::class, $bundles[0]->getName());
    }
}
