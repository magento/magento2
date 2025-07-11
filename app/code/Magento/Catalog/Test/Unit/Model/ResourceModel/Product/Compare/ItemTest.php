<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Compare;

use Magento\Catalog\Model\Product\Compare\Item as CompareItemModel;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item as CompareItemResource;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Model\ResourceModel\Product\Compare\Item::updateCustomerFromVisitor
 */
class ItemTest extends TestCase
{
    /**
     * @var CompareItemResource
     */
    private $itemResource;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Share|MockObject
     */
    private $shareMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var string
     */
    private $mainTable = 'catalog_compare_item';

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $this->selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();

        // Create Share mock
        $this->shareMock = $this->createMock(Share::class);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->itemResource = new CompareItemResource(
            $this->contextMock,
            null, // connectionName
            $this->shareMock,
            $this->storeManagerMock
        );
    }

    /**
     * Data provider for visitor ID test cases
     *
     * @return array
     */
    public function visitorIdDataProvider(): array
    {
        return [
            'visitor_id_null' => [
                'visitorId' => null,
                'customerId' => 123,
                'shouldExecuteQueries' => false,
                'expectedQueryCount' => 0
            ],
            'visitor_id_zero' => [
                'visitorId' => 0,
                'customerId' => 123,
                'shouldExecuteQueries' => false,
                'expectedQueryCount' => 0
            ],
            'visitor_id_positive' => [
                'visitorId' => 456,
                'customerId' => 123,
                'shouldExecuteQueries' => true,
                'expectedQueryCount' => 2
            ]
        ];
    }

    /**
     * Test updateCustomerFromVisitor with different visitor ID scenarios
     *
     * @dataProvider visitorIdDataProvider
     * @param mixed $visitorId
     * @param int $customerId
     * @param bool $shouldExecuteQueries
     * @param int $expectedQueryCount
     * @throws Exception
     */
    public function testUpdateCustomerFromVisitorWithVariousVisitorIds(
        $visitorId,
        int $customerId,
        bool $shouldExecuteQueries,
        int $expectedQueryCount
    ): void {
        $compareItemMock = $this->createMock(CompareItemModel::class);

        $compareItemMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $compareItemMock->expects($this->atLeastOnce())
            ->method('getVisitorId')
            ->willReturn($visitorId);

        if ($shouldExecuteQueries) {

            list($visitorItems, $customerItems) = $this->getQueryData($visitorId, $customerId);

            $fetchAllMatcher = $this->exactly($expectedQueryCount);

            $this->connectionMock->expects($fetchAllMatcher)
                ->method('fetchAll')
                ->willReturnCallback(function ($select) use ($fetchAllMatcher, $visitorItems, $customerItems) {
                    match ($fetchAllMatcher->numberOfInvocations()) {
                        1 => $this->assertSame($this->selectMock, $select),
                        2 => $this->assertSame($this->selectMock, $select),
                    };

                    return match ($fetchAllMatcher->numberOfInvocations()) {
                        1 => $visitorItems,
                        2 => $customerItems,
                        default => []
                    };
                });

            $this->connectionMock->expects($this->once())
                ->method('delete')
                ->with(
                    $this->mainTable,
                    $this->callback(function ($condition) {
                        return str_contains($condition, 'catalog_compare_item_id IN');
                    })
                );

            $updateMatcher = $this->exactly(2);

            $this->connectionMock->expects($updateMatcher)
                ->method('update')
                ->willReturnCallback(function ($table, $bind, $where) use ($updateMatcher, $customerId, $visitorId) {
                    $this->assertEquals($this->mainTable, $table);

                    $this->assertIsArray($bind);
                    $this->assertEquals($customerId, $bind['customer_id']);
                    $this->assertEquals($visitorId, $bind['visitor_id']);
                    $this->assertArrayHasKey('product_id', $bind);
                    $this->assertArrayHasKey('store_id', $bind);

                    $this->assertStringContainsString('catalog_compare_item_id=', $where);

                    match ($updateMatcher->numberOfInvocations()) {
                        1 => $this->assertEquals(101, $bind['product_id']),
                        2 => $this->assertEquals(102, $bind['product_id']),
                    };

                    return 1;
                });

            $this->connectionMock->expects($this->any())
                ->method('quoteInto')
                ->willReturnCallback(function ($sql, $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    } else {
                        $value = (string) $value;
                    }
                    return str_replace('?', $value, $sql);
                });
        } else {
            $this->connectionMock->expects($this->never())
                ->method('fetchAll');
            $this->connectionMock->expects($this->never())
                ->method('delete');
            $this->connectionMock->expects($this->never())
                ->method('update');
        }

        $result = $this->itemResource->updateCustomerFromVisitor($compareItemMock);
        $this->assertSame($this->itemResource, $result);
    }

    /**
     * Get query data executed during the test
     *
     * @param $visitorId
     * @param $customerId
     * @return array[]
     */
    private function getQueryData($visitorId, $customerId): array
    {
        $visitorItems = [
            [
                'catalog_compare_item_id' => 1,
                'product_id' => 101,
                'store_id' => 1,
                'customer_id' => null,
                'visitor_id' => $visitorId
            ],
            [
                'catalog_compare_item_id' => 2,
                'product_id' => 102,
                'store_id' => 1,
                'customer_id' => null,
                'visitor_id' => $visitorId
            ]
        ];

        $customerItems = [
            [
                'catalog_compare_item_id' => 3,
                'product_id' => 101,
                'store_id' => 1,
                'customer_id' => $customerId,
                'visitor_id' => 789
            ]
        ];

        return [$visitorItems, $customerItems];
    }
}
