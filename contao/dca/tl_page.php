<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_page']['fields']['rowJurisdiction'] = [
    'inputType' => 'select',
    'options' => ['DE', 'AT'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page']['rowJurisdictions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(2) NOT NULL default 'DE'",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rowWithdrawalPage'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rowCancellationPage'] = [
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rowRecipient'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'email', 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rowRetentionYears'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'natural', 'minval' => 1, 'maxlength' => 2, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 3',
];

$palette = PaletteManipulator::create()
    ->addLegend('row_legend', 'global_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField(['rowJurisdiction', 'rowRecipient', 'rowRetentionYears', 'rowWithdrawalPage', 'rowCancellationPage'], 'row_legend', PaletteManipulator::POSITION_APPEND)
;

// The Notification Center is optional
if (class_exists(Terminal42\NotificationCenterBundle\NotificationCenter::class)) {
    $GLOBALS['TL_DCA']['tl_page']['fields']['rowNotification'] = [
        'inputType' => 'select',
        'foreignKey' => "tl_nc_notification.title",
        'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
        'sql' => 'int(10) unsigned NOT NULL default 0',
    ];

    $palette->addField('rowNotification', 'row_legend', PaletteManipulator::POSITION_APPEND);
}

$palette
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;
