<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\App;

use Magento\TestFramework\App\ObjectManager\Environment\Developer;

class EnvironmentFactory extends \Magento\Framework\App\EnvironmentFactory
{
    public function createEnvironment()
    {
        return new Developer($this);
    }
}
