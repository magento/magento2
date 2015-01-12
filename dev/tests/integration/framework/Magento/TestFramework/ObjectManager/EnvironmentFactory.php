<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\ObjectManager;

use Magento\TestFramework\ObjectManager\Environment\Developer;

class EnvironmentFactory extends \Magento\Framework\ObjectManager\EnvironmentFactory
{
    public function createEnvironment()
    {
        return new Developer($this);
    }
}
