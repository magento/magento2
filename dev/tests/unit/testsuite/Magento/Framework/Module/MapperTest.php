<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Framework\Module\Mapper::createMapping
     */
    public function testPackageNameToModuleFullName()
    {
        $mapper = new Mapper();
        $modulesData = [
            'Vendor_A' => '{"name":"vendor/module-a"}',
            'Vendor_B' => '{"name":"vendor/module-b"}',
            'Vendor_C' => '{"name":"vendor/module-c"}',
        ];
        $mapper->createMapping($modulesData);
        $this->assertEquals('Vendor_A', $mapper->packageNameToModuleFullName('vendor/module-a'));
        $this->assertEquals('Vendor_B', $mapper->packageNameToModuleFullName('vendor/module-b'));
        $this->assertEquals('Vendor_C', $mapper->packageNameToModuleFullName('vendor/module-c'));
    }
}
