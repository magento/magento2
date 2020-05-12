<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class PackageInfoFactoryTest extends TestCase
{
    public function testCreate()
    {
        $fullModuleList = $this->createMock(FullModuleList::class);
        $reader = $this->createMock(Reader::class);
        $packageInfo = $this->createMock(PackageInfo::class);
        $returnValueMap = [
            [FullModuleList::class, [], $fullModuleList],
            [Reader::class, ['moduleList' => $fullModuleList], $reader],
            [PackageInfo::class, ['reader' => $reader], $packageInfo],
        ];
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap($returnValueMap);
        $factory = new PackageInfoFactory($objectManagerMock);

        $this->assertSame($packageInfo, $factory->create());
    }
}
