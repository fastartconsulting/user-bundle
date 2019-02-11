<?php

namespace FAC\UserBundle;

use FAC\UserBundle\DependencyInjection\FACUserExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FACUserBundle extends Bundle {

    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new FACUserExtension();
        }

        return $this->extension;
    }
}
