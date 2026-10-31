<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\DependencyInjection;

use Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter\DeclarationNotificationType;
use Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter\NotificationCenterSender;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class PdirContaoRightOfWithdrawalExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        // The Notification Center is optional, its classes must not be loaded without it
        if (class_exists(NotificationCenter::class)) {
            $container->autowire(NotificationCenterSender::class)->setAutoconfigured(true);
            $container->autowire(DeclarationNotificationType::class)->setAutoconfigured(true);
        }
    }
}
