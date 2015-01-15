<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

class DeploymentConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new DeploymentConfig([]);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $object = new DeploymentConfig([]);
        $this->assertSame([], $object->getData());
        $object = new DeploymentConfig(['Module_One' => '1', 'Module_Two' => false]);
        $this->assertSame(['Module_One' => 1, 'Module_Two' => 0], $object->getData());
    }

    /**
     * @param array $data
     * @dataProvider invalidDataDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect module name:
     */
    public function testInvalidData($data)
    {
        new DeploymentConfig($data);
    }

    /**
     * @return array
     */
    public function invalidDataDataProvider()
    {
        return [
            [['1', '2']],
            [['invalid_module' => 1]],
        ];
    }
}
