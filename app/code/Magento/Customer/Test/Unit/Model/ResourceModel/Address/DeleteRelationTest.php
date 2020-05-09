<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\DeleteRelation;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteRelationTest extends TestCase
{
    /** @var  DeleteRelation */
    protected $relation;

    protected function setUp(): void
    {
        $this->customerFactoryMock = $this->createPartialMock(
            CustomerFactory::class,
            ['create']
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            DeleteRelation::class
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping
     * @dataProvider getRelationDataProvider
     */
    public function testDeleteRelation($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        /** @var AbstractModel|MockObject $addressModel  */
        $addressModel = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsCustomerSaveTransaction', 'getId', 'getResource'])
            ->getMock();
        /** @var Customer|MockObject $customerModel */
        $customerModel = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultBilling', 'getDefaultShipping', 'getId'])
            ->getMock();

        $addressResource = $this->getMockForAbstractClass(
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
        $addressModel->expects($this->any())->method('getResource')->willReturn($addressResource);
        $addressModel->expects($this->any())->method('getId')->willReturn($addressId);
        $addressModel->expects($this->any())->method('getIsCustomerSaveTransaction')->willReturn(false);

        $customerModel->expects($this->any())->method("getDefaultBilling")->willReturn($isDefaultBilling);
        $customerModel->expects($this->any())->method("getDefaultShipping")->willReturn($isDefaultShipping);

        if ($addressId && ($isDefaultBilling || $isDefaultShipping)) {
            $customerId = 1;
            $addressResource->expects($this->exactly(2))->method('getConnection')->willReturn($connectionMock);
            $customerModel->expects($this->any())->method('getId')->willReturn(1);
            $conditionSql = "entity_id = $customerId";
            $connectionMock->expects($this->once())->method('quoteInto')
                ->with('entity_id = ?', $customerId)
                ->willReturn($conditionSql);
            $addressResource->expects($this->once())->method('getTable')
                ->with('customer_entity')
                ->willReturn('customer_entity');
            $toUpdate = [];
            if ($isDefaultBilling) {
                $toUpdate['default_billing'] = null;
            }
            if ($isDefaultShipping) {
                $toUpdate['default_shipping'] = null;
            }
            $connectionMock->expects($this->once())->method('update')->with(
                'customer_entity',
                $toUpdate,
                $conditionSql
            );
        }
        $result = $this->relation->deleteRelation($addressModel, $customerModel);
        $this->assertNull($result);
    }

    /**
     * Data provider for processRelation method
     *
     * @return array
     */
    public function getRelationDataProvider()
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
