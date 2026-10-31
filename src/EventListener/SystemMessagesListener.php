<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Doctrine\DBAL\Connection;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Warns on the back end start page about what would make the function fail.
 */
#[AsHook('getSystemMessages')]
class SystemMessagesListener
{
    public function __construct(
        private readonly SiteSettingsProvider $settings,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(): string
    {
        $messages = [];

        foreach ($this->settings->all() as $settings) {
            $enabled = $settings->isEnabled(DeclarationType::Withdrawal) || $settings->isEnabled(DeclarationType::Cancellation);

            if ($enabled && '' === $settings->recipient) {
                $messages[] = $this->translator->trans('backend.no_recipient', ['%root%' => $settings->rootPage], DeclarationType::TRANSLATION_DOMAIN);
            }
        }

        $failed = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . DeclarationRepository::TABLE . ' WHERE mailStatus = ?', [DeclarationRepository::MAIL_FAILED]);

        if ($failed > 0) {
            $messages[] = $this->translator->trans('backend.mails_failed', ['%count%' => $failed], DeclarationType::TRANSLATION_DOMAIN);
        }

        return implode('', array_map(static fn (string $message): string => '<p class="tl_error">' . htmlspecialchars($message) . '</p>', $messages));
    }
}
