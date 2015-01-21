<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class CheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetModulesData()
    {
        $modulesData = ['Vendor_A' => '{name: vendor/a}'];
        $mapperMock = $this->getMock('Magento\Framework\Module\Mapper', [], [], '', false);
        $mapperMock->expects($this->once())->method('createMapping')->with($modulesData);
        $checker = new Checker($mapperMock);
        $checker->setModulesData($modulesData);
    }
}
