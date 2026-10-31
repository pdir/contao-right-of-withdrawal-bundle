<?php

use Contao\DC_Table;

// The declaration itself can only be read. Only the processing fields are part of the palette.
$GLOBALS['TL_DCA']['tl_withdrawal_declaration'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notCopyable' => true,
        'notDeletable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'rootPage,sequenceNumber' => 'unique',
                'token' => 'unique',
                'receivedAt' => 'index',
                'ipHash' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['receivedAt DESC'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['receivedAt', 'type', 'name', 'contract', 'orderMatch', 'mailStatus', 'status'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'export' => [
                'href' => 'key=export',
                'icon' => 'theme_export.svg',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
            'resend' => [
                'href' => 'key=resend',
                'icon' => 'sync.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{processing_legend},status,notes',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'rootPage' => [
            'filter' => true,
            'foreignKey' => 'tl_page.title',
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'sequenceNumber' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'type' => [
            'filter' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_withdrawal_declaration']['types'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'jurisdiction' => [
            'filter' => true,
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'language' => [
            'filter' => true,
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'name' => [
            'search' => true,
            'sorting' => true,
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'contract' => [
            'search' => true,
            'sorting' => true,
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'contractPart' => [
            'sql' => 'text NULL',
        ],
        'email' => [
            'search' => true,
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'cancellationType' => [
            'reference' => &$GLOBALS['TL_LANG']['tl_withdrawal_declaration']['cancellationTypes'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'cancellationReason' => [
            'sql' => 'text NULL',
        ],
        'terminationDate' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'receivedAt' => [
            'sorting' => true,
            'flag' => 8,
            'eval' => ['rgxp' => 'datim'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'hash' => [
            'sql' => "char(64) NOT NULL default ''",
        ],
        'previousHash' => [
            'sql' => "char(64) NOT NULL default ''",
        ],
        'token' => [
            'eval' => ['doNotShow' => true],
            'sql' => 'char(64) NULL',
        ],
        'ipHash' => [
            'eval' => ['doNotShow' => true],
            'sql' => "char(64) NOT NULL default ''",
        ],
        'mailStatus' => [
            'filter' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_withdrawal_declaration']['mailStatuses'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'mailSentAt' => [
            'eval' => ['rgxp' => 'datim'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'orderId' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'orderMatch' => [
            'filter' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_withdrawal_declaration']['orderMatches'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'status' => [
            'inputType' => 'select',
            'filter' => true,
            'options' => ['received', 'in_progress', 'refund_started', 'done', 'rejected'],
            'reference' => &$GLOBALS['TL_LANG']['tl_withdrawal_declaration']['statuses'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'received'",
        ],
        'notes' => [
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
    ],
];
