<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupTest extends TestCase
{
    use MockCreationTrait;

    /** @var Group */
    protected $groupResourceModel;

    /** @var ResourceConnection|MockObject */
    protected $resource;

    /** @var Vat|MockObject */
    protected $customerVat;

    /** @var \Magento\Customer\Model\Group|MockObject */
    protected $groupModel;

    /** @var MockObject */
    protected $customersFactory;

    /** @var MockObject */
    protected $groupManagement;

    /** @var MockObject */
    protected $relationProcessorMock;

    /**
     * @var Snapshot|MockObject
     */
    private $snapshotMock;

    /**
     * Setting up dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->customerVat = $this->createMock(Vat::class);
        $this->customersFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);

        $this->groupModel = $this->createMock(\Magento\Customer\Model\Group::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->relationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );

        $this->snapshotMock = $this->createMock(
            Snapshot::class
        );

        $transactionManagerMock = $this->createMock(
            TransactionManagerInterface::class
        );
        $transactionManagerMock->expects($this->any())
            ->method('start')
            ->willReturn($this->createStub(AdapterInterface::class));
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            Group::class,
            [
                'context' => $contextMock,
                'groupManagement' => $this->groupManagement,
                'customersFactory' => $this->customersFactory,
                'entitySnapshot' => $this->snapshotMock
            ]
        );
    }

    /**
     * Test for save() method when we try to save entity with system's reserved ID.
     * @return void
     */
    public function testSaveWithReservedId()
    {
        $expectedId = 55;
        $this->snapshotMock->expects($this->once())->method('isModified')->willReturn(true);
        $this->snapshotMock->expects($this->once())->method('registerSnapshot')->willReturnSelf();

        $this->groupModel->expects($this->any())->method('getId')
            ->willReturn(\Magento\Customer\Model\Group::CUST_GROUP_ALL);
        $this->groupModel->expects($this->any())->method('getData')
            ->willReturn([]);
        $this->groupModel->expects($this->any())->method('isSaveAllowed')
            ->willReturn(true);
        $this->groupModel->expects($this->any())->method('getStoredData')
            ->willReturn([]);
        $this->groupModel->expects($this->once())->method('setId')
            ->with($expectedId);
        $this->groupModel->expects($this->once())->method('getCode')
            ->willReturn('customer_group_code');

        // Using createPartialMockWithReflection with stdClass to add custom methods
        $dbAdapter = $this->createPartialMockWithReflection(
            \stdClass::class,
            [
                'lastInsertId', 'describeTable', 'update', 'select', 'beginTransaction',
                'commit', 'rollBack', 'insert', 'fetchRow', 'prepareColumnValue',
                'quoteIdentifier', 'quote', 'quoteInto', 'insertFromSelect', 'query', 'deleteFromSelect',
                'getTransactionLevel'
            ]
        );
        $dbAdapter->method('lastInsertId')->willReturn($expectedId);
        $dbAdapter->method('describeTable')->willReturn(['customer_group_id' => []]);
        $dbAdapter->method('update')->willReturnSelf();
        $dbAdapter->method('beginTransaction')->willReturnSelf();
        $dbAdapter->method('commit')->willReturnSelf();
        $dbAdapter->method('rollBack')->willReturnSelf();
        $dbAdapter->method('insert')->willReturnSelf();
        $dbAdapter->method('fetchRow')->willReturn([]);
        $dbAdapter->method('prepareColumnValue')->willReturnArgument(2);
        $dbAdapter->method('quoteIdentifier')->willReturnArgument(0);
        $dbAdapter->method('quote')->willReturnArgument(0);
        $dbAdapter->method('quoteInto')->willReturnArgument(0);
        $dbAdapter->method('insertFromSelect')->willReturnSelf();
        $dbAdapter->method('query')->willReturnSelf();
        $dbAdapter->method('deleteFromSelect')->willReturnSelf();
        $dbAdapter->method('getTransactionLevel')->willReturn(1);
        $selectMock = $this->createStub(Select::class);
        $dbAdapter->method('select')->willReturn($selectMock);
        $selectMock->method('from')->willReturnSelf();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $this->groupResourceModel->save($this->groupModel);
    }

    /**
     * Test for delete() method when we try to save entity with system's reserved ID.
     *
     * @return void
     */
    public function testDelete()
    {
        $dbAdapter = $this->createStub(AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $customer = $this->createPartialMockWithReflection(
            Customer::class,
            ['getStoreId', 'setGroupId', '__wakeup', 'load', 'getId', 'save']
        );
        $customerId = 1;
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $customer->expects($this->once())->method('load')->with($customerId)->willReturnSelf();
        $defaultCustomerGroup = $this->createPartialMock(\Magento\Customer\Model\Group::class, ['getId']);
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')
            ->willReturn($defaultCustomerGroup);
        $defaultCustomerGroup->expects($this->once())->method('getId')
            ->willReturn(1);
        $customer->expects($this->once())->method('setGroupId')->with(1);
        $customerCollection = $this->createMock(Collection::class);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $customerCollection->expects($this->once())->method('load')->willReturn([$customer]);
        $this->customersFactory->expects($this->once())->method('create')
            ->willReturn($customerCollection);

        $this->relationProcessorMock->expects($this->once())->method('delete');
        $this->groupModel->expects($this->any())->method('getData')->willReturn(['data' => 'value']);
        $this->groupResourceModel->delete($this->groupModel);
    }

    /**
     * Test that _beforeDelete throws exception when trying to delete a default group
     *
     * @return void
     */
    public function testBeforeDeleteThrowsExceptionWhenGroupUsesAsDefault(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('You can\'t delete group "Default Group".');

        $dbAdapter = $this->createMock(AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        // Mock the group to use as default
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(true);
        
        $this->groupModel->expects($this->once())
            ->method('getCode')
            ->willReturn('Default Group');

        $this->groupModel->expects($this->any())
            ->method('getData')
            ->willReturn(['customer_group_code' => 'Default Group']);

        $this->groupResourceModel->delete($this->groupModel);
    }

    /**
     * Test that _beforeDelete allows deletion of non-default groups
     *
     * @return void
     */
    public function testBeforeDeleteAllowsNonDefaultGroup(): void
    {
        $dbAdapter = $this->createMock(AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        // Mock the group to NOT use as default
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(false);

        $this->groupModel->expects($this->any())
            ->method('getData')
            ->willReturn(['customer_group_code' => 'Custom Group']);

        // Mock customer collection (for _afterDelete)
        $customerCollection = $this->createMock(Collection::class);
        $customerCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $customerCollection->expects($this->once())
            ->method('load')
            ->willReturn([]);
        
        $this->customersFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerCollection);

        $this->relationProcessorMock->expects($this->once())
            ->method('delete');

        // Should not throw exception
        $result = $this->groupResourceModel->delete($this->groupModel);
        
        $this->assertSame($this->groupResourceModel, $result);
    }

    /**
     * Test that _beforeSave correctly truncates multibyte characters
     *
     * @param string $input
     * @param string $expected
     * @return void
     */
    #[DataProvider('multibyteCharacterProvider')]
    public function testBeforeSaveTruncatesMultibyteCharacters(string $input, string $expected): void
    {
        $this->snapshotMock->expects($this->once())->method('isModified')->willReturn(true);
        $this->snapshotMock->expects($this->once())->method('registerSnapshot')->willReturnSelf();

        $this->groupModel->expects($this->any())->method('getId')->willReturn(1);
        $this->groupModel->expects($this->any())->method('getData')->willReturn([]);
        $this->groupModel->expects($this->any())->method('isSaveAllowed')->willReturn(true);
        $this->groupModel->expects($this->any())->method('getStoredData')->willReturn([]);
        
        // Key test: verify that setCode is called with correctly truncated multibyte string
        $this->groupModel->expects($this->once())
            ->method('getCode')
            ->willReturn($input);
        
        $this->groupModel->expects($this->once())
            ->method('setCode')
            ->with($expected);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $dbAdapter = $this->createMock(AdapterInterface::class);
        $dbAdapter->method('describeTable')->willReturn(['customer_group_id' => []]);
        $dbAdapter->method('update')->willReturnSelf();
        $dbAdapter->method('select')->willReturn($selectMock);
        $selectMock->method('from')->willReturnSelf();
        
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $this->groupResourceModel->save($this->groupModel);
    }

    /**
     * Data provider for multibyte character truncation tests
     *
     * @return array
     */
    public static function multibyteCharacterProvider(): array
    {
        return [
            'ascii_within_limit' => [
                'input' => str_repeat('a', 32),
                'expected' => str_repeat('a', 32)
            ],
            'ascii_over_limit' => [
                'input' => str_repeat('a', 40),
                'expected' => str_repeat('a', 32)
            ],
            'multibyte_umlaut_within_limit' => [
                'input' => str_repeat('ö', 32),
                'expected' => str_repeat('ö', 32)
            ],
            'multibyte_umlaut_over_limit' => [
                'input' => str_repeat('ö', 40),
                'expected' => str_repeat('ö', 32)
            ],
            'multibyte_chinese_within_limit' => [
                'input' => str_repeat('中', 32),
                'expected' => str_repeat('中', 32)
            ],
            'multibyte_chinese_over_limit' => [
                'input' => str_repeat('中', 40),
                'expected' => str_repeat('中', 32)
            ],
            'mixed_multibyte' => [
                'input' => str_repeat('aö', 20), // 40 characters
                'expected' => str_repeat('aö', 16) // 32 characters
            ]
        ];
    }
}
