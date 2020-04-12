<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Category\StateKey;
use Magento\Customer\Model\Session;
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
     * @var StateKey
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->model = new StateKey($this->storeManagerMock, $this->customerSessionMock);
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Category\StateKey::toString
     * @covers \Magento\Catalog\Model\Layer\Category\StateKey::__construct
     */
    public function testToString()
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())->method('getId')->will($this->returnValue('1'));

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getId')->will($this->returnValue('2'));

        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue('3'));

        $this->assertEquals('STORE_2_CAT_1_CUSTGROUP_3', $this->model->toString($categoryMock));
    }
}
