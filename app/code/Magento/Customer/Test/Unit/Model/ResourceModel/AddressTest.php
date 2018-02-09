<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Test\Unit\Model\ResourceModel\SubResourceModelAddress */
    protected $addressResource;

    /** @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactory;

    /** @var \Magento\Eav\Model\Entity\Type */
    protected $eavConfigType;

    /** @var  Snapshot|\PHPUnit_Framework_MockObject_MockObject */
    protected $entitySnapshotMock;

    /** @var  RelationComposite|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityRelationCompositeMock;

    protected function setUp()
    {
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );

        $this->entityRelationCompositeMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class
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
        /** @var $address \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject */
        $address = $this->createPartialMock(
            \Magento\Customer\Model\Address::class,
            [
                '__wakeup',
                'getId',
                'getEntityTypeId',
                'getIsDefaultBilling',
                'getIsDefaultShipping',
                'hasDataChanges',
                'validateBeforeSave',
                'beforeSave',
                'afterSave',
                'isSaveAllowed'
            ]
        );
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

        $attributeLoaderMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\AttributeLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressResource->setAttributeLoader($attributeLoaderMock);
        $this->addressResource->save($address);
    }

    /**
     * Data provider for testSave method
     *
     * @return array
     */
    public function getSaveDataProvider()
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
     * @return \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareResource()
    {
        $dbSelect = $this->createMock(\Magento\Framework\DB\Select::class);
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();

        $dbAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
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

        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource->expects($this->any())->method('getConnection')->will($this->returnValue($dbAdapter));
        $resource->expects($this->any())->method('getTableName')->will($this->returnValue('customer_address_entity'));

        return $resource;
    }

    /**
     * Prepare Eav config mock object
     *
     * @return \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareEavConfig()
    {
        $attributeMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getAttributeCode', 'getBackend', '__wakeup']
        );
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('entity_id');
        $attributeMock->expects($this->any())
            ->method('getBackend')
            ->willReturn(
                $this->createMock(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
            );

        $this->eavConfigType = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Type::class,
            ['getEntityIdField', 'getId', 'getEntityTable', '__wakeup']
        );
        $this->eavConfigType->expects($this->any())->method('getEntityIdField')->willReturn(false);
        $this->eavConfigType->expects($this->any())->method('getId')->willReturn(false);
        $this->eavConfigType->expects($this->any())->method('getEntityTable')->willReturn('customer_address_entity');

        $eavConfig = $this->createPartialMock(
            \Magento\Eav\Model\Config::class,
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
            ->willReturnMap([
                [$this->eavConfigType, 'entity_type_id', $attributeMock],
                [$this->eavConfigType, 'attribute_set_id', $attributeMock],
                [$this->eavConfigType, 'created_at', $attributeMock],
                [$this->eavConfigType, 'updated_at', $attributeMock],
                [$this->eavConfigType, 'parent_id', $attributeMock],
                [$this->eavConfigType, 'increment_id', $attributeMock],
                [$this->eavConfigType, 'entity_id', $attributeMock],
            ]);

        return $eavConfig;
    }

    /**
     * Prepare validator mock object
     *
     * @return \Magento\Framework\Validator\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareValidatorFactory()
    {
        $validatorMock = $this->createPartialMock(\Magento\Framework\Validator::class, ['isValid']);
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $validatorFactory = $this->createPartialMock(\Magento\Framework\Validator\Factory::class, ['createValidator']);
        $validatorFactory->expects($this->any())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validatorMock);

        return $validatorFactory;
    }

    protected function prepareCustomerFactory()
    {
        $this->customerFactory = $this->createPartialMock(\Magento\Customer\Model\CustomerFactory::class, ['create']);
        return $this->customerFactory;
    }

    public function testGetType()
    {
        $this->assertSame($this->eavConfigType, $this->addressResource->getEntityType());
    }
}

/**
 * Class SubResourceModelAddress
 * Mock method getAttributeLoader
 * @package Magento\Customer\Test\Unit\Model\ResourceModel
 * @codingStandardsIgnoreStart
 */
class SubResourceModelAddress extends \Magento\Customer\Model\ResourceModel\Address
{
    protected $attributeLoader;

    public function loadAllAttributes($object = null)
    {
        return $this->getAttributeLoader()->loadAllAttributes($this, $object);
    }

    public function setAttributeLoader($attributeLoader)
    {
        $this->attributeLoader = $attributeLoader;
    }

    protected function getAttributeLoader()
    {
        return $this->attributeLoader;
    }
}
// @codingStandardsIgnoreEnd
