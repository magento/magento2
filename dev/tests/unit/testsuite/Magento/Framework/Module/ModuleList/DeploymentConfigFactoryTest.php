<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

class DeploymentConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Magento\Framework\Module\ModuleList\DeploymentConfigFactory
     */
    protected $object;

    public function testCreate()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $helper->getObject('Magento\Framework\Module\ModuleList\DeploymentConfigFactory');
        $this->object->create(['Module_One' => 0, 'Module_Two' =>1]);
    }
}
