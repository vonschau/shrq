<?php

namespace SHRQ\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SHRQUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
