<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

use Magento\CatalogSearch\Model\Layer\Search\StateKey;

class StateKeyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryFactoryMock;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Search\StateKey
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->queryFactoryMock = $this->createMock(\Magento\Search\Model\QueryFactory::class);

        $this->model = new StateKey($this->storeManagerMock, $this->customerSessionMock, $this->queryFactoryMock);
    }

    /**
     * @covers \Magento\CatalogSearch\Model\Layer\Search\StateKey::toString
     * @covers \Magento\CatalogSearch\Model\Layer\Search\StateKey::__construct
     */
    public function testToString()
    {
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $categoryMock->expects($this->once())->method('getId')->willReturn('1');

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn('2');

        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn('3');

        $queryMock = $this->createPartialMock(\Magento\Search\Model\Query::class, ['getId']);
        $queryMock->expects($this->once())->method('getId')->willReturn('4');
        $this->queryFactoryMock->expects($this->once())->method('get')->willReturn($queryMock);

        $this->assertEquals('Q_4_STORE_2_CAT_1_CUSTGROUP_3', $this->model->toString($categoryMock));
    }
}
