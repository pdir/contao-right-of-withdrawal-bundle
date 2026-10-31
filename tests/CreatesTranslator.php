<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests;

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Loads the real translation files, so the tests also cover their keys.
 */
trait CreatesTranslator
{
    private function createTranslator(string $locale = 'de'): Translator
    {
        $translator = new Translator($locale);
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->setFallbackLocales(['en']);

        foreach (['de', 'en'] as $language) {
            $translator->addResource('yaml', __DIR__ . '/../translations/pdir_withdrawal.' . $language . '.yaml', $language, 'pdir_withdrawal');
        }

        return $translator;
    }
}
