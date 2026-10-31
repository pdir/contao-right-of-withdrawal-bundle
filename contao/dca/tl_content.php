<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['withdrawal_form'] = '{type_legend},type,headline;{config_legend},rowFunction;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['rowFunction'] = [
    'inputType' => 'select',
    'options' => ['withdrawal', 'cancellation'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['rowFunctions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default 'withdrawal'",
];
