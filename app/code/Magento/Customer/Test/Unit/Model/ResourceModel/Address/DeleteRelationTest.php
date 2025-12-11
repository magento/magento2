<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class DeleteRelationTest extends TestCase
{
    use MockCreationTrait;

    /** @var  DeleteRelation */
    protected $relation;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->relation = (new ObjectManagerHelper($this))->getObject(
            DeleteRelation::class
        );
    }

    /**
     * @param $addressId
     * @param $isDefaultBilling
     * @param $isDefaultShipping */
    #[DataProvider('getRelationDataProvider')]
    public function testDeleteRelation($addressId, $isDefaultBilling, $isDefaultShipping)
    {
        /** @var AbstractModel|MockObject $addressModel  */
        $addressModel = $this->createPartialMockWithReflection(
            AbstractModel::class,
            [
                'getIsCustomerSaveTransaction',
                'getId',
                'getResource'
            ]
        );
        /** @var Customer|MockObject $customerModel */
        $customerModel = $this->createPartialMockWithReflection(
            Customer::class,
            [
                'getDefaultBilling',
                'getDefaultShipping',
                'getId'
            ]
        );

        $addressResource = $this->createMock(
            AbstractDb::class
        );
        $connectionMock = $this->createMock(
            AdapterInterface::class
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
