<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Model\Layer\Search\StateKey;
use Magento\Customer\Model\Session;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateKeyTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $queryFactoryMock;

    /**
     * @var StateKey
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->queryFactoryMock = $this->createMock(QueryFactory::class);

        $this->model = new StateKey($this->storeManagerMock, $this->customerSessionMock, $this->queryFactoryMock);
    }

    /**
     * @covers \Magento\CatalogSearch\Model\Layer\Search\StateKey::toString
     * @covers \Magento\CatalogSearch\Model\Layer\Search\StateKey::__construct
     */
    public function testToString()
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())->method('getId')->willReturn('1');

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn('2');

        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn('3');

        $queryMock = $this->createPartialMock(Query::class, ['getId']);
        $queryMock->expects($this->once())->method('getId')->willReturn('4');
        $this->queryFactoryMock->expects($this->once())->method('get')->willReturn($queryMock);

        $this->assertEquals('Q_4_STORE_2_CAT_1_CUSTGROUP_3', $this->model->toString($categoryMock));
    }
}
