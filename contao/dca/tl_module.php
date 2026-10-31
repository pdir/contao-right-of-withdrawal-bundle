<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['withdrawal_link'] = '{title_legend},name,headline,type;{config_legend},rowFunction;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['rowFunction'] = [
    'inputType' => 'select',
    'options' => ['withdrawal', 'cancellation'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['rowFunctions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default 'withdrawal'",
];
