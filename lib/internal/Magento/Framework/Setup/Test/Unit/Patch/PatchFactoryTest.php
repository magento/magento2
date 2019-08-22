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
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->patchFactory = $objectManager->getObject(
            PatchFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass should implement Magento\Framework\Setup\Patch\PatchInterface interface
     */
    public function testCreateNonPatchInterface()
    {
        $patchNonPatchInterface = $this->createMock(\stdClass::class);
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('\\stdClass')
            ->willReturn($patchNonPatchInterface);

        $this->patchFactory->create(\stdClass::class);
    }
}
