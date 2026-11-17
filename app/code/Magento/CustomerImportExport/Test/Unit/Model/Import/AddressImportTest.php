<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\CustomerImportExport\Model\Import\Address;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify address persistence through the public import entry point.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressImportTest extends TestCase
{
    /**
     * @var Mysql&MockObject
     */
    private Mysql $connection;

    /**
     * @var Processor&MockObject
     */
    private Processor $indexer;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Mysql::class);
        $this->indexer = $this->createMock(Processor::class);
        $renderer = $this->createStub(SelectRenderer::class);
        $this->connection->method('select')
            ->willReturnCallback(fn (): Select => new Select($this->connection, $renderer));
        $this->connection->method('quoteInto')
            ->willReturnCallback(
                static fn (string $text, int $value): string => str_replace('?', (string) $value, $text)
            );
    }

    public function testImportUsesAllocatedIdsForAttributesAndDefaults(): void
    {
        $first = $this->getPreparedAddress('First');
        $second = $this->getPreparedAddress('Second');
        unset($second['defaults']['customer_entity'][1]['default_shipping']);
        $update = $this->getPreparedAddress('Updated', 7);
        unset($update['defaults']['customer_entity'][1]['default_billing']);
        $model = $this->getImportModel([$first, $second, $update]);

        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');
        $this->connection->expects($this->never())->method('insertMultiple');
        $locked = false;
        $this->connection->expects($this->once())->method('fetchOne')
            ->willReturnCallback(function (Select $select) use (&$locked): string {
                $this->assertTrue($select->getPart(Select::FOR_UPDATE));
                $this->assertSame(['(entity_id = 1)'], $select->getPart(Select::WHERE));
                $locked = true;
                return '1';
            });
        $this->connection->expects($this->exactly(2))->method('insert')
            ->willReturnCallback(function (string $table, array $row) use (&$locked): int {
                $this->assertTrue($locked);
                $this->assertSame('customer_address_entity', $table);
                $this->assertArrayNotHasKey('entity_id', $row);
                return 1;
            });
        $this->connection->expects($this->exactly(2))->method('lastInsertId')
            ->with('customer_address_entity')
            ->willReturnOnConsecutiveCalls('101', '205');
        $writes = [];
        $this->connection->expects($this->exactly(3))->method('insertOnDuplicate')
            ->willReturnCallback(
                static function (string $table, array $rows, array $fields) use (&$writes): int {
                    $writes[] = [$table, $rows, $fields];
                    return 1;
                }
            );
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame([
            ['customer_address_entity', [
                'entity_id' => 7, 'parent_id' => 1, 'firstname' => 'Updated',
            ], [2 => 'firstname']],
            ['customer_address_entity_varchar', [
                ['entity_id' => 101, 'attribute_id' => 11, 'value' => 'First'],
                ['entity_id' => 205, 'attribute_id' => 11, 'value' => 'Second'],
                ['entity_id' => 7, 'attribute_id' => 11, 'value' => 'Updated'],
            ], ['value']],
            ['customer_entity', [
                'entity_id' => 1, 'default_billing' => 205, 'default_shipping' => 7,
            ], ['default_billing', 'default_shipping']],
        ], $writes);
        $this->assertSame(2, $model->getCreatedItemsCount());
        $this->assertSame(1, $model->getUpdatedItemsCount());
    }

    #[DataProvider('persistenceFailureProvider')]
    public function testImportRollsBackAndPropagatesFailures(string $failureStage): void
    {
        $model = $this->getImportModel([$this->getPreparedAddress('First')]);
        $exception = new \RuntimeException('Persistence failed');
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->never())->method('commit');
        $this->connection->expects($this->once())->method('rollBack');
        $this->connection->method('fetchOne')
            ->willReturnCallback(static function () use ($failureStage, $exception): string {
                if ($failureStage === 'locks') {
                    throw $exception;
                }
                return '1';
            });
        $this->connection->expects($failureStage === 'locks' ? $this->never() : $this->once())->method('insert')
            ->willReturnCallback(static function () use ($failureStage, $exception): int {
                if ($failureStage === 'entity') {
                    throw $exception;
                }
                return 1;
            });
        $this->connection->method('lastInsertId')->willReturn('101');
        $this->connection->method('insertOnDuplicate')
            ->willReturnCallback(static function (string $table) use ($failureStage, $exception): int {
                if (($failureStage === 'attributes' && $table === 'customer_address_entity_varchar')
                    || ($failureStage === 'defaults' && $table === 'customer_entity')
                ) {
                    throw $exception;
                }
                return 1;
            });
        $this->indexer->expects($this->never())->method('markIndexerAsInvalid');

        $this->expectExceptionObject($exception);
        $model->importData();
    }

    public static function persistenceFailureProvider(): array
    {
        return [
            'customer locks' => ['locks'],
            'new entity insert' => ['entity'],
            'custom attributes' => ['attributes'],
            'customer defaults' => ['defaults'],
        ];
    }

    public function testLocksCustomersOnceInNumericOrderBeforeAnyInsert(): void
    {
        $rows = [];
        foreach ([20, 3, 20, 9] as $customerId) {
            $row = $this->getPreparedAddress('New');
            $row['entity_row_new']['parent_id'] = $customerId;
            $row['attributes'] = $row['defaults'] = [];
            $rows[] = $row;
        }
        $model = $this->getImportModel($rows);
        $lockedIds = [];
        $this->connection->expects($this->exactly(3))->method('fetchOne')
            ->willReturnCallback(function (Select $select) use (&$lockedIds): string {
                $this->assertTrue($select->getPart(Select::FOR_UPDATE));
                $condition = $select->getPart(Select::WHERE)[0];
                $customerId = (int) filter_var($condition, FILTER_SANITIZE_NUMBER_INT);
                $lockedIds[] = $customerId;
                return (string) $customerId;
            });
        $this->connection->expects($this->exactly(4))->method('insert')
            ->willReturnCallback(function () use (&$lockedIds): int {
                $this->assertSame([3, 9, 20], $lockedIds);
                return 1;
            });
        $this->connection->expects($this->exactly(4))->method('lastInsertId')
            ->willReturnOnConsecutiveCalls('101', '102', '103', '104');
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame(4, $model->getCreatedItemsCount());
    }

    public function testImportAllocatesIdsAcrossBunches(): void
    {
        $first = $this->getPreparedAddress('First');
        $second = $this->getPreparedAddress('Second');
        $first['attributes'] = $first['defaults'] = [];
        $second['attributes'] = $second['defaults'] = [];
        $model = $this->getImportModel([$first, $second], 1);
        $this->connection->expects($this->exactly(2))->method('beginTransaction');
        $this->connection->expects($this->exactly(2))->method('commit');
        $this->connection->expects($this->never())->method('rollBack');
        $this->connection->expects($this->exactly(2))->method('insert');
        $this->connection->expects($this->exactly(2))->method('lastInsertId')
            ->willReturnOnConsecutiveCalls('101', '305');
        $this->connection->expects($this->never())->method('insertMultiple');
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame(2, $model->getCreatedItemsCount());
    }

    #[DataProvider('deleteBehaviorProvider')]
    public function testDeleteDoesNotAllocateIdsOrLockCustomers(string $behavior, string $action): void
    {
        $model = $this->getImportModel([[]], 100, [$action]);
        $model->setParameters(['behavior' => $behavior]);
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->expects($this->never())->method('select');
        $this->connection->expects($this->never())->method('fetchOne');
        $this->connection->expects($this->never())->method('insert');
        $this->connection->expects($this->never())->method('lastInsertId');
        $this->connection->expects($this->once())->method('delete')
            ->with('customer_address_entity', ['entity_id IN (?)' => [7]]);
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame(1, $model->getDeletedItemsCount());
    }

    public static function deleteBehaviorProvider(): array
    {
        return [
            'delete import' => [Import::BEHAVIOR_DELETE, ''],
            'custom delete row' => [Import::BEHAVIOR_CUSTOM, 'delete'],
        ];
    }

    public function testCustomBehaviorLocksOnlyCustomersWithAddUpdateRows(): void
    {
        $new = $this->getPreparedAddress('New');
        $update = $this->getPreparedAddress('Updated', 7);
        $update['entity_row_update']['parent_id'] = 9;
        $new['attributes'] = $new['defaults'] = [];
        $update['attributes'] = $update['defaults'] = [];
        $deleteOnly = $this->getPreparedAddress('Deleted', 7);
        $deleteOnly['entity_row_update']['parent_id'] = 2;
        $deleteShared = $this->getPreparedAddress('Deleted', 7);
        $model = $this->getImportModel(
            [$new, $update, $deleteOnly, $deleteShared],
            100,
            ['', '', 'delete', 'delete']
        );
        $model->setParameters(['behavior' => Import::BEHAVIOR_CUSTOM]);
        $lockedIds = [];
        $this->connection->expects($this->exactly(2))->method('fetchOne')
            ->willReturnCallback(function (Select $select) use (&$lockedIds): string {
                $this->assertTrue($select->getPart(Select::FOR_UPDATE));
                $condition = $select->getPart(Select::WHERE)[0];
                $customerId = (int) filter_var($condition, FILTER_SANITIZE_NUMBER_INT);
                $lockedIds[] = $customerId;
                return (string) $customerId;
            });
        $this->connection->expects($this->once())->method('insert')
            ->willReturnCallback(function () use (&$lockedIds): int {
                $this->assertSame([1, 9], $lockedIds);
                return 1;
            });
        $this->connection->expects($this->once())->method('lastInsertId')->willReturn('101');
        $this->connection->expects($this->once())->method('insertOnDuplicate')
            ->with('customer_address_entity', $update['entity_row_update']);
        $this->connection->expects($this->once())->method('delete')
            ->with('customer_address_entity', ['entity_id IN (?)' => [7, 7]]);
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame(1, $model->getCreatedItemsCount());
        $this->assertSame(1, $model->getUpdatedItemsCount());
        $this->assertSame(2, $model->getDeletedItemsCount());
    }

    #[DataProvider('optionalAddressProvider')]
    public function testCompositeCustomerOnlyRowsDoNotLockOrWriteAddresses(bool $withAddress): void
    {
        $rows = [];
        if ($withAddress) {
            $address = $this->getPreparedAddress('New');
            $address['attributes'] = $address['defaults'] = [];
            $rows[] = $address;
        }
        $addressCount = count($rows);
        $customerOnly = $this->getPreparedAddress('CustomerOnly', 7);
        $customerOnly['entity_row_update']['parent_id'] = 2;
        $rows[] = $customerOnly;
        $model = $this->getImportModel($rows, 100, [], [$addressCount]);
        $model->setCustomerAttributes(['firstname', 'lastname']);
        $model->expects($this->exactly($addressCount))->method('_getCustomerId')
            ->with('customer1@example.com', 'base');
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $locked = false;
        $this->connection->expects($this->exactly($addressCount))->method('fetchOne')
            ->willReturnCallback(function (Select $select) use (&$locked): string {
                $this->assertTrue($select->getPart(Select::FOR_UPDATE));
                $this->assertSame(['(entity_id = 1)'], $select->getPart(Select::WHERE));
                $locked = true;
                return '1';
            });
        $this->connection->expects($this->exactly($addressCount))->method('insert')
            ->willReturnCallback(function () use (&$locked): int {
                $this->assertTrue($locked);
                return 1;
            });
        $this->connection->expects($this->exactly($addressCount))->method('lastInsertId')->willReturn('101');
        $this->connection->expects($this->never())->method('insertOnDuplicate');
        $this->connection->expects($this->never())->method('delete');
        $this->indexer->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertTrue($model->importData());
        $this->assertSame($addressCount, $model->getCreatedItemsCount());
        $this->assertSame(0, $model->getUpdatedItemsCount());
        $this->assertSame(0, $model->getDeletedItemsCount());
    }

    public static function optionalAddressProvider(): array
    {
        return [
            'customer-only bunch' => [false],
            'mixed bunch' => [true],
        ];
    }

    /**
     * @param array $preparedRows
     * @param int $bunchSize
     * @param array $actions
     * @param array $customerOnlyRows
     * @return Address&MockObject
     */
    private function getImportModel(
        array $preparedRows,
        int $bunchSize = 100,
        array $actions = [],
        array $customerOnlyRows = []
    ): Address&MockObject {
        $model = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'prepareCustomerData', 'validateRow',
                '_prepareDataForUpdate', '_getCustomerId', '_getCustomerEntity',
            ])
            ->getMock();
        $model->expects($this->once())->method('prepareCustomerData');
        $model->method('validateRow')->willReturn(true);
        $model->method('_prepareDataForUpdate')->willReturnOnConsecutiveCalls(...$preparedRows);
        $model->setParameters(['behavior' => Import::BEHAVIOR_ADD_UPDATE]);
        $rows = [];
        $idsByEmail = [];
        foreach ($preparedRows as $rowNumber => $prepared) {
            $customerId = $prepared['entity_row_new']['parent_id'] ?? $prepared['entity_row_update']['parent_id'] ?? 1;
            $email = 'customer' . $customerId . '@example.com';
            $idsByEmail[$email] = $customerId;
            $rows[] = [
                '_entity_id' => in_array($rowNumber, $customerOnlyRows, true) ? '' : 7,
                '_email' => $email,
                '_website' => 'base',
                '_action' => $actions[$rowNumber] ?? '',
            ];
        }
        $model->method('_getCustomerId')->willReturnCallback(
            static fn (string $email): int => $idsByEmail[$email]
        );
        $customerResource = $this->createStub(CustomerResource::class);
        $customerResource->method('getTable')->willReturn('customer_entity');
        $customer = $this->createStub(Customer::class);
        $customer->method('getResource')->willReturn($customerResource);
        $model->method('_getCustomerEntity')->willReturn($customer);
        $bunches = array_chunk($rows, $bunchSize, true);
        $dataSource = $this->createStub(Data::class);
        $dataSource->method('getNextUniqueBunch')
            ->willReturnOnConsecutiveCalls(...array_merge($bunches, [null], $bunches, [null]));
        $dataSource->method('getIterator')->willReturn(new \ArrayIterator());
        $errorAggregator = $this->createStub(ProcessingErrorAggregatorInterface::class);
        $errorAggregator->method('hasToBeTerminated')->willReturn(false);

        foreach ([
            '_connection' => $this->connection,
            '_entityTable' => 'customer_address_entity',
            '_dataSourceModel' => $dataSource,
            'errorAggregator' => $errorAggregator,
            'indexerProcessor' => $this->indexer,
        ] as $property => $value) {
            (new \ReflectionProperty(Address::class, $property))->setValue($model, $value);
        }

        return $model;
    }

    /**
     * @param string $value
     * @param int $addressId
     * @return array
     */
    private function getPreparedAddress(string $value, int $addressId = 0): array
    {
        $row = ['entity_id' => $addressId, 'parent_id' => 1, 'firstname' => $value];
        return [
            'entity_row_new' => $addressId ? [] : $row,
            'entity_row_update' => $addressId ? $row : [],
            'attributes' => ['customer_address_entity_varchar' => [$addressId => [11 => $value]]],
            'defaults' => [
                'customer_entity' => [1 => ['default_billing' => $addressId, 'default_shipping' => $addressId]],
            ],
        ];
    }
}
