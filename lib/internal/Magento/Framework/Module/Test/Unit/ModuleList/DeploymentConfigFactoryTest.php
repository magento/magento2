<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\Test\Unit\ModuleList;

use Magento\Framework\Module\ModuleList\DeploymentConfigFactory;

class DeploymentConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeploymentConfigFactory
     */
    protected $object;

    public function testCreate()
    {
        $this->object = new DeploymentConfigFactory();
        $this->assertInstanceOf(
            'Magento\Framework\Module\ModuleList\DeploymentConfig',
            $this->object->create(['Module_One' => 0, 'Module_Two' =>1])
        );
    }
}
