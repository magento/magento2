<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Resource\Address */
    protected $addressResource;

    /** @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactory;

    protected function setUp()
    {
        $this->addressResource = (new ObjectManagerHelper($this))->getObject(
            'Magento\Customer\Model\Resource\Address',
            [
                'resource' => $this->prepareResource(),
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
        /** @var $customer \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject */
        $customer = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['__wakeup', 'setDefaultBilling', 'setDefaultShipping', 'save', 'load'],
            [],
            '',
            false
        );
        $customer->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->customerFactory->expects($this->any())
            ->method('create')
            ->willReturn($customer);

        /** @var $address \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject */
        $address = $this->getMock(
            'Magento\Customer\Model\Address',
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
            ],
            [],
            '',
            false
        );
        $address->expects($this->once())->method('hasDataChanges')->willReturn(true);
        $address->expects($this->once())->method('isSaveAllowed')->willReturn(true);
        $address->expects($this->once())->method('validateBeforeSave');
        $address->expects($this->once())->method('beforeSave');
        $address->expects($this->once())->method('afterSave');
        $address->expects($this->any())->method('getEntityTypeId')->willReturn('3');
        $address->expects($this->any())->method('getId')->willReturn($addressId);
        $address->expects($this->any())->method('getIsDefaultShipping')->willReturn($isDefaultShipping);
        $address->expects($this->any())->method('getIsDefaultBilling')->willReturn($isDefaultBilling);
        if ($addressId && ($isDefaultBilling || $isDefaultShipping)) {
            if ($isDefaultBilling) {
                $customer->expects($this->once())->method('setDefaultBilling')->with($addressId);
            }
            if ($isDefaultShipping) {
                $customer->expects($this->once())->method('setDefaultShipping')->with($addressId);
            }
            $customer->expects($this->once())->method('save');
        } else {
            $customer->expects($this->never())->method('setDefaultBilling');
            $customer->expects($this->never())->method('setDefaultShipping');
            $customer->expects($this->never())->method('save');
        }
        $this->addressResource->setType('customer_address');
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
     * @return \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareResource()
    {
        $dbSelect = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();

        $dbAdapter = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
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

        $resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
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
        $attributeMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            ['getAttributeCode', 'getBackend', '__wakeup'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('entity_id');
        $attributeMock->expects($this->any())
            ->method('getBackend')
            ->willReturn(
                $this->getMock(
                    'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend', [], [], '', false
                )
            );

        $eavConfigType = $this->getMock(
            'Magento\Eav\Model\Entity\Type',
            ['getEntityIdField', 'getId', 'getEntityTable', '__wakeup'],
            [],
            '',
            false
        );
        $eavConfigType->expects($this->any())->method('getEntityIdField')->willReturn(false);
        $eavConfigType->expects($this->any())->method('getId')->willReturn(false);
        $eavConfigType->expects($this->any())->method('getEntityTable')->willReturn('customer_address_entity');

        $eavConfig = $this->getMock(
            'Magento\Eav\Model\Config',
            ['getEntityType', 'getEntityAttributeCodes', 'getAttribute'],
            [],
            '',
            false
        );
        $eavConfig->expects($this->any())
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($eavConfigType);
        $eavConfig->expects($this->any())
            ->method('getEntityAttributeCodes')
            ->with($eavConfigType)
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
                [$eavConfigType, 'entity_type_id', $attributeMock],
                [$eavConfigType, 'attribute_set_id', $attributeMock],
                [$eavConfigType, 'created_at', $attributeMock],
                [$eavConfigType, 'updated_at', $attributeMock],
                [$eavConfigType, 'parent_id', $attributeMock],
                [$eavConfigType, 'increment_id', $attributeMock],
                [$eavConfigType, 'entity_id', $attributeMock],
            ]);

        return $eavConfig;
    }

    /**
     * Prepare validator mock object
     *
     * @return \Magento\Core\Model\Validator\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareValidatorFactory()
    {
        $validatorMock = $this->getMock('Magento\Framework\Validator', ['isValid'], [], '', false);
        $validatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $validatorFactory = $this->getMock(
            'Magento\Core\Model\Validator\Factory',
            ['createValidator'],
            [],
            '',
            false
        );
        $validatorFactory->expects($this->any())
            ->method('createValidator')
            ->with('customer_address', 'save')
            ->willReturn($validatorMock);

        return $validatorFactory;
    }

    protected function prepareCustomerFactory()
    {
        $this->customerFactory = $this->getMock('Magento\Customer\Model\CustomerFactory', ['create'], [], '', false);
        return $this->customerFactory;
    }
}
