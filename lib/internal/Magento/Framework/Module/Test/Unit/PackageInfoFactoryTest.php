<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\PackageInfoFactory;

class PackageInfoFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $returnValueMap = [
            ['Magento\Framework\Module\FullModuleList', [], $fullModuleList],
            ['Magento\Framework\Module\Dir\Reader', ['moduleList' => $fullModuleList], $reader],
            ['Magento\Framework\Module\PackageInfo', ['reader' => $reader], $packageInfo],
        ];
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap($returnValueMap));
        $factory = new PackageInfoFactory($objectManagerMock);

        $this->assertSame($packageInfo, $factory->create());
    }
}
