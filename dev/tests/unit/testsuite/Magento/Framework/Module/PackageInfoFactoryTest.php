<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class PackageInfoFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $moduleList = $this->getMock('Magento\Setup\Model\ModuleList', [], [], '', false);
        $reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $returnValueMap = [
            ['Magento\Setup\Model\ModuleList', [], $moduleList],
            ['Magento\Framework\Module\Dir\Reader', ['moduleList' => $moduleList], $reader],
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
