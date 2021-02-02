<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Patch\PatchFactory;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Framework\Setup\Patch\PatchInterface;

/**
 * Class PatchFactoryTest
 * @package Magento\Framework\Setup\Test\Unit\Patch
 */
class PatchFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PatchFactory
     */
    private $patchFactory;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
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

    /**
     */
    public function testCreateNonPatchInterface()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('stdClass should implement Magento\\Framework\\Setup\\Patch\\PatchInterface interface');

        $patchNonPatchInterface = $this->createMock(\stdClass::class);
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('\\stdClass')
            ->willReturn($patchNonPatchInterface);

        $this->patchFactory->create(\stdClass::class);
    }
}
