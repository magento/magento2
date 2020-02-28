<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Quote\Model\ResourceModel\Quote\Item as ResourceQuoteItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class Resource Quote Item Test
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @var ResourceQuoteItem
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|MockObject
     */
    private $quoteItemMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ObjectRelationProcessor|MockObject
     */
    private $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp()
    {
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->quoteItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->connectionMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ]);

        $this->objectRelationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            ResourceQuoteItem::class,
            [
                'context' => $contextMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            $this->model
        );
    }

    public function testSaveNotModifiedItem(): void
    {
        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }

    public function testSaveSavedBeforeItem(): void
    {
        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }

    public function testSaveModifiedItem(): void
    {
        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }
}
