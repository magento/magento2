<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Framework\Setup\Patch\PatchInterface;

/**
 * Class PatchHistoryTest
 * Test for PatchHistory
 */
class PatchHistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->patchHistory = $objectManager->getObject(
            PatchHistory::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
            ]
        );
    }

    /**
     * Test fix non-applied patch
     */
    public function testFixPatch()
    {
        /** @var PatchInterface|\PHPUnit\Framework\MockObject\MockObject $patch1 */
        $patch1 = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from');
        $adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchCol')->willReturn([]);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn(PatchHistory::TABLE_NAME);
        $adapterMock->expects($this->once())->method('insert')
            ->with(PatchHistory::TABLE_NAME, [PatchHistory::CLASS_NAME => get_class($patch1)]);
        $this->patchHistory->fixPatch(get_class($patch1));
    }

    /**
     */
    public function testFixAppliedPatch()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('"Patch [a-zA-Z0-9\\_]+ cannot be applied twice"');

        /** @var PatchInterface|\PHPUnit\Framework\MockObject\MockObject $patch1 */
        $patch1 = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from');
        $adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchCol')->willReturn([get_class($patch1)]);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn(PatchHistory::TABLE_NAME);
        $adapterMock->expects($this->never())->method('insert');
        $this->patchHistory->fixPatch(get_class($patch1));
    }

    /**
     */
    public function testFixPatchTwice()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('"Patch [a-zA-Z0-9\\_]+ cannot be applied twice"');

        /** @var PatchInterface|\PHPUnit\Framework\MockObject\MockObject $patch1 */
        $patch = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn(PatchHistory::TABLE_NAME);
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())->method('from');
        $adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchCol')->willReturn([]);
        $adapterMock->expects($this->once())->method('insert');

        $this->patchHistory->fixPatch(get_class($patch));
        $this->patchHistory->fixPatch(get_class($patch));
    }
}
