<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class DeclarationNotificationType implements NotificationTypeInterface
{
    public const NAME = 'withdrawal_declaration';

    public function __construct(private readonly TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        $definitions = [
            $this->factory->create(EmailTokenDefinition::class, 'email', 'withdrawal.email'),
            $this->factory->create(EmailTokenDefinition::class, 'recipient', 'withdrawal.recipient'),
        ];

        foreach (['number', 'type', 'legal_basis', 'name', 'contract', 'contract_part', 'cancellation_type', 'cancellation_reason', 'termination_date', 'received_at', 'hash'] as $token) {
            $definitions[] = $this->factory->create(TextTokenDefinition::class, $token, 'withdrawal.' . $token);
        }

        return $definitions;
    }
}
