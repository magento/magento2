<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item as ResourceQuoteItem;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
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
    private const STUB_QUOTE_ID = "12";

    private const STUB_PRODUCT_ID = "1";

    private const STUB_IS_VIRTUAL = "0";

    private const STUB_STORE_ID = 1;

    private const STUB_SKU = "SimpleSku";

    private const STUB_PRODUCT_QTY = 2;

    private const STUB_PRODUCT_WEIGHT = "1.0";

    /**
     * @var ResourceQuoteItem
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var QuoteItem|MockObject
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
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->connectionMock = $this->createPartialMock(Mysql::class, [
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
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
                    ->method('getResources')
                    ->willReturn($this->resourceMock);
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
            AbstractDb::class,
            $this->model
        );
    }

    /**
     * Test case save quote item not modified
     *
     * @dataProvider testDataQuoteItemProvider
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testSaveNotModifiedItem(): void
    {
        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals(
            $this->model,
            $this->model->save($this->quoteItemMock)
        );
    }

    /**
     * Test case save quote item before
     *
     * @dataProvider testDataQuoteItemProvider
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testSaveSavedBeforeItem(): void
    {
        $this->quoteItemMock->expects($this->once())
                            ->method('setIsOptionsSaved')
                            ->willReturn(false);
        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals(
            $this->model,
            $this->model->save($this->quoteItemMock)
        );
    }

    /**
     * Test case save Modified quote item
     *
     * @dataProvider testDataQuoteItemProvider
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testSaveModifiedItem(): void
    {
        $this->quoteItemMock->expects($this->once())
                            ->method('setIsOptionsSaved');
        $this->quoteItemMock->expects($this->any())
            ->method('saveItemOptions');
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->assertEquals(
            $this->model,
            $this->model->save($this->quoteItemMock)
        );
    }

    /**
     * Provider quote item test data
     *
     * @return array|array[]
     */
    public function testDataQuoteItemProvider(): array
    {
        return [
            [
                'quote_id' => self::STUB_QUOTE_ID,
                'product_id' => self::STUB_PRODUCT_ID,
                'is_virtual' => self::STUB_IS_VIRTUAL,
                'parent_item_id' => null,
                'store_id' => self::STUB_STORE_ID,
                'sku' => self::STUB_SKU,
                'weight' => self::STUB_PRODUCT_WEIGHT,
                'qty' => self::STUB_PRODUCT_QTY
            ]
        ];
    }
}
