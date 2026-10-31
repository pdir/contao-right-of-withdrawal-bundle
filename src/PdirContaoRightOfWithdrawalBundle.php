<?php

namespace Pdir\ContaoRightOfWithdrawalBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PdirContaoRightOfWithdrawalBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
