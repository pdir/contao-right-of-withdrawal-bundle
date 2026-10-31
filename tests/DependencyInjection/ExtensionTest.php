<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\DependencyInjection;

use Pdir\ContaoRightOfWithdrawalBundle\Backend\DeclarationActions;
use Pdir\ContaoRightOfWithdrawalBundle\Controller\ContentElement\WithdrawalFormController;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationSubmitter;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettings;
use Pdir\ContaoRightOfWithdrawalBundle\DependencyInjection\PdirContaoRightOfWithdrawalExtension;
use Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter\NotificationCenterSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtensionTest extends TestCase
{
    public function testLoadsServices(): void
    {
        $container = new ContainerBuilder();
        (new PdirContaoRightOfWithdrawalExtension())->load([], $container);

        $this->assertTrue($container->getDefinition(DeclarationActions::class)->isPublic());
        $this->assertTrue($container->getDefinition(WithdrawalFormController::class)->isPublic());
        $this->assertSame('%kernel.secret%', $container->getDefinition(DeclarationSubmitter::class)->getArgument('$secret'));
        $this->assertTrue($container->getDefinition(SiteSettings::class)->hasTag('container.excluded'));
        $this->assertFalse($container->hasDefinition(NotificationCenterSender::class));
    }
}
