<?php

use Pdir\ContaoRightOfWithdrawalBundle\Backend\DeclarationActions;

$GLOBALS['BE_MOD']['content']['right_of_withdrawal'] = [
    'tables' => ['tl_withdrawal_declaration'],
    'export' => [DeclarationActions::class, 'export'],
    'resend' => [DeclarationActions::class, 'resend'],
];
