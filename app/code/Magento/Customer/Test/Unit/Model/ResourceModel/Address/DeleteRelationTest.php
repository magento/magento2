<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddressTest
 */
class DeleteRelationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Customer\Model\ResourceModel\Address\DeleteRelation */
    protected $relation;

    protected function setUp()
    {
        $this->customerFactoryMock = $this->getMock(
            \Magento\Customer\Model\CustomerFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            \Magento\Customer\Model\ResourceModel\Address\DeleteRelation::class
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
        /** @var AbstractModel | \PHPUnit_Framework_MockObject_MockObject $addressModel  */
        $addressModel = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Customer\Model\Customer | \PHPUnit_Framework_MockObject_MockObject $customerModel */
        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultBilling', 'getDefaultShipping', 'getId'])
            ->getMock();

        $addressResource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['getConnection', 'getTable']
        );
        $connectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
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
        $this->relation->deleteRelation($addressModel, $customerModel);
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
