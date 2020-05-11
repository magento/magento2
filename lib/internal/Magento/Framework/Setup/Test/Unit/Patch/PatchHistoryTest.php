<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PatchHistoryTest extends TestCase
{
    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var ResourceConnection|MockObject
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
        /** @var PatchInterface|MockObject $patch1 */
        $patch1 = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $selectMock = $this->createMock(Select::class);
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

    public function testFixAppliedPatch()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessageMatches('"Patch [a-zA-Z0-9\_]+ cannot be applied twice"');
        /** @var PatchInterface|MockObject $patch1 */
        $patch1 = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from');
        $adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchCol')->willReturn([get_class($patch1)]);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn(PatchHistory::TABLE_NAME);
        $adapterMock->expects($this->never())->method('insert');
        $this->patchHistory->fixPatch(get_class($patch1));
    }

    public function testFixPatchTwice()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessageMatches('"Patch [a-zA-Z0-9\_]+ cannot be applied twice"');
        /** @var PatchInterface|MockObject $patch1 */
        $patch = $this->getMockForAbstractClass(PatchInterface::class);
        /** @var AdapterInterface|MockObject $adapterMock */
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturn(PatchHistory::TABLE_NAME);
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from');
        $adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $adapterMock->expects($this->once())->method('fetchCol')->willReturn([]);
        $adapterMock->expects($this->once())->method('insert');

        $this->patchHistory->fixPatch(get_class($patch));
        $this->patchHistory->fixPatch(get_class($patch));
    }
}
