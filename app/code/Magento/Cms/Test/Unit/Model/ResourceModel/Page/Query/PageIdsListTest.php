<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Query;

use Magento\Cms\Model\ResourceModel\Page\Query\PageIdsList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageIdsListTest extends TestCase
{

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
    }

    /**
     * @param $blockEntityIds
     * @param $pageEntityIds
     * @param $blockIdentifiers
     * @dataProvider getDataProvider
     */
    public function testExecute($blockEntityIds, $pageEntityIds, $blockIdentifiers)
    {
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        if (count($blockEntityIds)) {
            $this->resourceMock->expects($this->any())
                ->method('getTableName')
                ->willReturnOnConsecutiveCalls('cms_page', 'cms_block');
            $this->selectMock->expects($this->any())
                ->method('orWhere')
                ->willReturnSelf();

            $this->connectionMock->expects($this->exactly(2))
                ->method('fetchCol')
                ->willReturnOnConsecutiveCalls($blockIdentifiers, $pageEntityIds);

            $this->connectionMock->expects($this->exactly(2))
                ->method('select')
                ->willReturn($this->selectMock);
        } else {
            $this->connectionMock->expects($this->once())
                ->method('select')
                ->willReturn($this->selectMock);
            $this->selectMock->expects($this->any())
                ->method('where')
                ->willReturnSelf();
            $this->connectionMock->expects($this->once())
                ->method('fetchCol')
                ->willReturn($pageEntityIds);
        }
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->with()
            ->willReturn($this->connectionMock);

        $pageIdsList = new PageIdsList(
            $this->resourceMock
        );

        $this->assertSame($pageEntityIds, $pageIdsList->execute($blockEntityIds));
    }

    /**
     * Execute data provider
     *
     * @return array
     */
    public static function getDataProvider(): array
    {
        return [
            [[1, 2, 3], [1], ['test1', 'test2', 'test3']],
            [[1, 2, 3], [], []],
            [[], [], []]
        ];
    }
}
