<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class DependencyGraphFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = new DependencyGraphFactory();
        $mapper = $this->getMock('Magento\Framework\Module\Mapper', [], [], '', false);
        $valueMap = [
            ['vendor/module-a', 'Vendor_A'],
            ['vendor/module-b', 'Vendor_B'],
            ['vendor/module-c', 'Vendor_C'],
            ['vendor/module-d', 'Vendor_D'],
            ['vendor/module-e', 'Vendor_E'],
        ];
        $mapper->expects($this->any())
            ->method('packageNameToModuleFullName')
            ->will($this->returnValueMap($valueMap));
        $modulesData = [
            'Vendor_A' => '{"require": {"vendor/module-b":"0.1", "vendor/module-c":"0.1"}}',
            'Vendor_B' => '{"require": {"vendor/module-d":"0.1"}}',
            'Vendor_C' => '{"require": {"vendor/module-e":"0.1"}}',
            'Vendor_D' => '{"require": {"vendor/module-a":"0.1"}}',
            'Vendor_E' => '{"require": {"vendor/module-b":"0.1", "vendor/module-d":"0.1"}}',
        ];
        $relations = [
            'Vendor_A' => ['Vendor_B' => 'Vendor_B', 'Vendor_C' => 'Vendor_C'],
            'Vendor_B' => ['Vendor_D' => 'Vendor_D'],
            'Vendor_C' => ['Vendor_E' => 'Vendor_E'],
            'Vendor_D' => ['Vendor_A' => 'Vendor_A'],
            'Vendor_E' => ['Vendor_B' => 'Vendor_B', 'Vendor_D' => 'Vendor_D'],
        ];
        $graph = $factory->create($mapper, $modulesData);
        $this->assertInstanceOf('Magento\Framework\Module\DependencyGraph', $graph);
        $this->assertEquals($relations, $graph->getRelations());
    }
}
