<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Block\Checkout\Success;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuccessTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Success
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $contextMock;
    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createPartialMockWithReflection(
            Generic::class,
            ['getOrderIds']
        );
        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->contextMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->expects($this->once())
            ->method('getStoreManager')->willReturn($this->storeManagerMock);
        $this->model = $objectManager->getObject(
            Success::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testGetOrderIdsWithoutId()
    {
        $this->sessionMock->method('getOrderIds')->willReturn(null);

        $this->assertFalse($this->model->getOrderIds());
    }

    public function testGetOrderIdsWithEmptyIdsArray()
    {
        $this->sessionMock->method('getOrderIds')->willReturn([]);

        $this->assertFalse($this->model->getOrderIds());
    }

    public function testGetOrderIds()
    {
        $ids = [100, 102, 103];
        $this->sessionMock->method('getOrderIds')->willReturn($ids);

        $this->assertEquals($ids, $this->model->getOrderIds());
    }

    public function testGetContinueUrl()
    {
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn('Expected Result');

        $this->assertEquals('Expected Result', $this->model->getContinueUrl());
    }
}
