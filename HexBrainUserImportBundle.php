<?php

namespace HexBrain\Bundle\UserImportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HexBrainUserImportBundle extends Bundle
{

    public function getParent()
    {
        return 'OroUserBundle';
    }
}
