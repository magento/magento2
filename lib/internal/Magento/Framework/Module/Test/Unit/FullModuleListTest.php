<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\FullModuleList;

class FullModuleListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleList
     */
    private $moduleList;

    protected function setUp()
    {
        $loaderMock = $this->createMock(\Magento\Framework\Module\ModuleList\Loader::class);
        $modules = [
            'Vendor_A' => ['data' => 'a'],
            'Vendor_B' => ['data' => 'b'],
            'Vendor_C' => ['data' => 'c'],
        ];
        $loaderMock->expects($this->once())->method('load')->will($this->returnValue($modules));
        $this->moduleList = new FullModuleList($loaderMock);
    }

    public function testGetAll()
    {
        $expect = [
            'Vendor_A' => ['data' => 'a'],
            'Vendor_B' => ['data' => 'b'],
            'Vendor_C' => ['data' => 'c'],
        ];
        $this->assertEquals($expect, $this->moduleList->getAll());
        // call once more to make sure it's cached
        $this->moduleList->getAll();
    }

    public function testGetOne()
    {
        $expect = ['data' => 'b'];
        $this->assertEquals($expect, $this->moduleList->getOne('Vendor_B'));
    }

    public function testGetNames()
    {
        $expect = ['Vendor_A', 'Vendor_B', 'Vendor_C'];
        $this->assertEquals($expect, $this->moduleList->getNames());
    }

    public function testHasTrue()
    {
        $this->assertTrue($this->moduleList->has('Vendor_A'));
    }

    public function testHasFalse()
    {
        $this->assertFalse($this->moduleList->has('No_Such_Module'));
    }
}
