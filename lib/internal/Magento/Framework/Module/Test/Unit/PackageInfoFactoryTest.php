<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\PackageInfoFactory;

class PackageInfoFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $fullModuleList = $this->getMock(\Magento\Framework\Module\FullModuleList::class, [], [], '', false);
        $reader = $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false);
        $packageInfo = $this->getMock(\Magento\Framework\Module\PackageInfo::class, [], [], '', false);
        $returnValueMap = [
            [\Magento\Framework\Module\FullModuleList::class, [], $fullModuleList],
            [\Magento\Framework\Module\Dir\Reader::class, ['moduleList' => $fullModuleList], $reader],
            [\Magento\Framework\Module\PackageInfo::class, ['reader' => $reader], $packageInfo],
        ];
        $objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap($returnValueMap));
        $factory = new PackageInfoFactory($objectManagerMock);

        $this->assertSame($packageInfo, $factory->create());
    }
}
