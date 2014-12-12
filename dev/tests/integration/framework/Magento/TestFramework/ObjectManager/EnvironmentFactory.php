<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
