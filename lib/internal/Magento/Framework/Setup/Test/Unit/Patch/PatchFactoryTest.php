<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\PatchFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PatchFactoryTest extends TestCase
{
    /**
     * @var PatchFactory
     */
    private $patchFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->patchFactory = $objectManager->getObject(
            PatchFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    public function testCreateNonPatchInterface()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'stdClass should implement Magento\Framework\Setup\Patch\PatchInterface interface'
        );
        $patchNonPatchInterface = $this->createMock(\stdClass::class);
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('\\stdClass')
            ->willReturn($patchNonPatchInterface);

        $this->patchFactory->create(\stdClass::class);
    }
}
