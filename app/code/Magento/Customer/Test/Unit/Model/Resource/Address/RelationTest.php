<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Resource\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddressTest
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Customer\Model\CustomerFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactoryMock;

    /** @var  \Magento\Customer\Model\Resource\Address\Relation */
    protected $relation;

    protected function setUp()
    {
        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            'Magento\Customer\Model\Resource\Address\Relation',
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
        $addressModel = $this->getMock(
            'Magento\Framework\Model\AbstractModel',
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
        $customerModel = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['__wakeup', 'setDefaultBilling', 'setDefaultShipping', 'save', 'load'],
            [],
            '',
            false
        );
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
            if ($isDefaultBilling) {
                $customerModel->expects($this->once())->method('setDefaultBilling')->with($addressId);
            }
            if ($isDefaultShipping) {
                $customerModel->expects($this->once())->method('setDefaultShipping')->with($addressId);
            }
            $customerModel->expects($this->once())->method('save');
        } else {
            $customerModel->expects($this->never())->method('setDefaultBilling');
            $customerModel->expects($this->never())->method('setDefaultShipping');
            $customerModel->expects($this->never())->method('save');
        }
        $this->relation->processRelation($addressModel);
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
