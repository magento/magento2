<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\AttributeLoaderInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Validator;
use Magento\Framework\Validator\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /** @var \Magento\Customer\Test\Unit\Model\ResourceModel\SubResourceModelAddress */
    protected $addressResource;

    /** @var CustomerFactory|MockObject */
    protected $customerFactory;

    /** @var Type */
    protected $eavConfigType;

    /** @var  Snapshot|MockObject */
    protected $entitySnapshotMock;

    /** @var  RelationComposite|MockObject */
    protected $entityRelationCompositeMock;

    protected function setUp(): void
    {
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );

        $this->entityRelationCompositeMock = $this->createMock(
            RelationComposite::class
        );

        $this->addressResource = (new ObjectManagerHelper($this))->getObject(
            \Magento\Customer\Test\Unit\Model\ResourceModel\SubResourceModelAddress::class,
            [
                'resource' => $this->prepareResource(),
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->entityRelationCompositeMock,
                'eavConfig' => $this->prepareEavConfig(),
                'validatorFactory' => $this->prepareValidatorFactory(),
                'customerFactory' => $this->prepareCustomerFactory()
            ]
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     *
     * @dataProvider getSaveDataProvider
     */
    public function testSave($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        /** @var $address \Magento\Customer\Model\Address|\PHPUnit\Framework\MockObject\MockObject */
        $address = $this->getMockBuilder(Address::class)
            ->addMethods(['getIsDefaultBilling', 'getIsDefaultShipping'])
            ->onlyMethods(
                [
                    '__wakeup',
                    'getId',
                    'getEntityTypeId',
                    'hasDataChanges',
                    'validateBeforeSave',
                    'beforeSave',
                    'afterSave',
                    'isSaveAllowed'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->willReturn(true);
        $this->entityRelationCompositeMock->expects($this->once())->method('processRelations');
        $address->expects($this->once())->method('isSaveAllowed')->willReturn(true);
        $address->expects($this->once())->method('validateBeforeSave');
        $address->expects($this->once())->method('beforeSave');
        $address->expects($this->once())->method('afterSave');
        $address->expects($this->any())->method('getEntityTypeId')->willReturn('3');
        $address->expects($this->any())->method('getId')->willReturn($addressId);
        $address->expects($this->any())->method('getIsDefaultShipping')->willReturn($isDefaultShipping);
        $address->expects($this->any())->method('getIsDefaultBilling')->willReturn($isDefaultBilling);
        $this->addressResource->setType('customer_address');

        $attributeLoaderMock = $this->getMockBuilder(AttributeLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->addressResource->setAttributeLoader($attributeLoaderMock);
        $this->addressResource->save($address);
    }

    /**
     * Data provider for testSave method
     *
     * @return array
     */
    public static function getSaveDataProvider()
    {
        return [
            [null, true, true],
            [1, true, true],
            [1, true, false],
            [1, false, true],
            [1, false, false],
        ];
    }

    /**
     * Prepare resource mock object
     *
     * @return ResourceConnection|MockObject
     */
    protected function prepareResource()
    {
        $dbSelect = $this->createMock(Select::class);
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();

        $dbAdapter = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dbAdapter->expects($this->any())
            ->method('describeTable')
            ->with('customer_address_entity')
            ->willReturn(
                [
                    'entity_type_id',
                    'attribute_set_id',
                    'created_at',
                    'updated_at',
                    'parent_id',
                    'increment_id',
                    'entity_id',
                ]
            );
        $dbAdapter->expects($this->any())->method('lastInsertId');
        $dbAdapter->expects($this->any())->method('select')->willReturn($dbSelect);

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);
        $resource->expects($this->any())->method('getTableName')->willReturn('customer_address_entity');

        return $resource;
    }

    /**
     * Prepare Eav config mock object
     *
     * @return Config|MockObject
     */
    protected function prepareEavConfig()
    {
        $attributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeCode', 'getBackend', '__wakeup']
        );
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('entity_id');
        $attributeMock->expects($this->any())
            ->method('getBackend')
            ->willReturn(
                $this->createMock(AbstractBackend::class)
            );

        $this->eavConfigType = $this->createPartialMock(
            Type::class,
            ['getEntityIdField', 'getId', 'getEntityTable', '__wakeup']
        );
        $this->eavConfigType->expects($this->any())->method('getEntityIdField')->willReturn(false);
        $this->eavConfigType->expects($this->any())->method('getId')->willReturn(false);
        $this->eavConfigType->expects($this->any())->method('getEntityTable')->willReturn('customer_address_entity');

        $eavConfig = $this->createPartialMock(
            Config::class,
            ['getEntityType', 'getEntityAttributeCodes', 'getAttribute']
        );
        $eavConfig->expects($this->any())
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($this->eavConfigType);
        $eavConfig->expects($this->any())
            ->method('getEntityAttributeCodes')
            ->with($this->eavConfigType)
            ->willReturn(
                [
                    'entity_type_id',
                    'attribute_set_id',
                    'created_at',
                    'updated_at',
                    'parent_id',
                    'increment_id',
                    'entity_id',
                ]
            );
        $eavConfig->expects($this->any())
            ->method('getAttribute')
            ->willReturnMap(
                [
                    [$this->eavConfigType, 'entity_type_id', $attributeMock],
                    [$this->eavConfigType, 'attribute_set_id', $attributeMock],
                    [$this->eavConfigType, 'created_at', $attributeMock],
                    [$this->eavConfigType, 'updated_at', $attributeMock],
                    [$this->eavConfigType, 'parent_id', $attributeMock],
                    [$this->eavConfigType, 'increment_id', $attributeMock],
                    [$this->eavConfigType, 'entity_id', $attributeMock],
                ]
            );

        return $eavConfig;
    }

    /**
     * Prepare validator mock object
     *
     * @return Factory|MockObject
     */
    protected function prepareValidatorFactory()
    {
        $validatorMock = $this->createPartialMock(Validator::class, ['isValid']);
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $validatorFactory = $this->createPartialMock(Factory::class, ['createValidator']);
        $validatorFactory->expects($this->any())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validatorMock);

        return $validatorFactory;
    }

    /**
     * @return CustomerFactory|MockObject
     */
    protected function prepareCustomerFactory()
    {
        $this->customerFactory = $this->createPartialMock(CustomerFactory::class, ['create']);
        return $this->customerFactory;
    }

    public function testGetType()
    {
        $this->assertSame($this->eavConfigType, $this->addressResource->getEntityType());
    }
}

/**
 * Mock method getAttributeLoader
 * @codingStandardsIgnoreStart
 */
class SubResourceModelAddress extends \Magento\Customer\Model\ResourceModel\Address
{
    protected $attributeLoader;

    /**
     * @param null $object
     * @return \Magento\Customer\Model\ResourceModel\Address|AbstractEntity
     */
    public function loadAllAttributes($object = null)
    {
        return $this->getAttributeLoader()->loadAllAttributes($this, $object);
    }

    /**
     * @param $attributeLoader
     */
    public function setAttributeLoader($attributeLoader)
    {
        $this->attributeLoader = $attributeLoader;
    }

    /**
     * @return AttributeLoaderInterface
     */
    protected function getAttributeLoader()
    {
        return $this->attributeLoader;
    }
}
// @codingStandardsIgnoreEnd
