<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\PackageInfoFactory;

class PackageInfoFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $fullModuleList = $this->createMock(\Magento\Framework\Module\FullModuleList::class);
        $reader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $packageInfo = $this->createMock(\Magento\Framework\Module\PackageInfo::class);
        $returnValueMap = [
            [\Magento\Framework\Module\FullModuleList::class, [], $fullModuleList],
            [\Magento\Framework\Module\Dir\Reader::class, ['moduleList' => $fullModuleList], $reader],
            [\Magento\Framework\Module\PackageInfo::class, ['reader' => $reader], $packageInfo],
        ];
        $objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap($returnValueMap);
        $factory = new PackageInfoFactory($objectManagerMock);

        $this->assertSame($packageInfo, $factory->create());
    }
}
