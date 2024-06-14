<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\Relation;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{
    /** @var  CustomerFactory|MockObject */
    protected $customerFactoryMock;

    /** @var  Relation */
    protected $relation;

    protected function setUp(): void
    {
        $this->customerFactoryMock = $this->createPartialMock(
            CustomerFactory::class,
            ['create']
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            Relation::class,
            [
                'customerFactory' => $this->customerFactoryMock
            ]
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider getRelationDataProvider
     */
    public function testProcessRelation($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        $addressModel = $this->getMockBuilder(Address::class)
            ->addMethods(['getIsDefaultBilling', 'getIsDefaultShipping', 'getIsCustomerSaveTransaction'])
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
        $customerModel = $this->getMockBuilder(Customer::class)
            ->addMethods(['setDefaultBilling', 'setDefaultShipping'])
            ->onlyMethods(
                [
                    '__wakeup',
                    'save',
                    'load',
                    'getResource',
                    'getId',
                    'getDefaultShippingAddress',
                    'getDefaultBillingAddress'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $customerResource = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['getConnection', 'getTable']
        );
        $connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['update', 'quoteInto']
        );
        $customerModel->expects($this->any())->method('getResource')->willReturn($customerResource);
        $addressModel->expects($this->any())->method('getId')->willReturn($addressId);
        $addressModel->expects($this->any())->method('getIsDefaultShipping')->willReturn($isDefaultShipping);
        $addressModel->expects($this->any())->method('getIsDefaultBilling')->willReturn($isDefaultBilling);
        $addressModel->expects($this->any())->method('getIsCustomerSaveTransaction')->willReturn(false);

        $customerModel->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($customerModel);

        if ($addressId && ($isDefaultBilling || $isDefaultShipping)) {
            $customerId = 1;
            $customerResource->expects($this->exactly(2))->method('getConnection')->willReturn($connectionMock);
            $customerModel->expects($this->any())->method('getId')->willReturn(1);
            $conditionSql = "entity_id = $customerId";
            $connectionMock->expects($this->once())->method('quoteInto')
                ->with('entity_id = ?', $customerId)
                ->willReturn($conditionSql);
            $customerResource->expects($this->once())->method('getTable')
                ->with('customer_entity')
                ->willReturn('customer_entity');
            $toUpdate = [];
            if ($isDefaultBilling) {
                $toUpdate['default_billing'] = $addressId;
            }
            if ($isDefaultShipping) {
                $toUpdate['default_shipping'] = $addressId;
            }
            $connectionMock->expects($this->once())->method('update')->with(
                'customer_entity',
                $toUpdate,
                $conditionSql
            );
        }
        $result = $this->relation->processRelation($addressModel);
        $this->assertNull($result);
    }

    /**
     * Data provider for processRelation method
     *
     * @return array
     */
    public static function getRelationDataProvider()
    {
        return [
            [null, true, true],
            [1, true, true],
            [1, true, false],
            [1, false, true],
            [1, false, false],
        ];
    }
}
