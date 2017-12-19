<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\Success;

class SuccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Success
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->sessionMock = $this->createPartialMock(\Magento\Framework\Session\SessionManagerInterface::class, [
                'getOrderIds', 'start', 'writeClose', 'isSessionExists', 'getSessionId', 'getName', 'setName',
                'destroy', 'clearStorage', 'getCookieDomain', 'getCookiePath', 'getCookieLifetime', 'setSessionId',
                'regenerateId', 'expireSessionCookie', 'getSessionIdForHost', 'isValidForHost', 'isValidForPath',
                '__wakeup'
            ]);
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock->expects($this->once())->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->contextMock->expects($this->once())
            ->method('getStoreManager')->will($this->returnValue($this->storeManagerMock));
        $this->model = $objectManager->getObject(
            \Magento\Multishipping\Block\Checkout\Success::class,
            [
                'context' => $this->contextMock
            ]);
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
        $this->sessionMock->method('getOrderIds')->willReturn($ids);;

        $this->assertEquals($ids, $this->model->getOrderIds());
    }

    public function testGetContinueUrl()
    {
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue('Expected Result'));

        $this->assertEquals('Expected Result', $this->model->getContinueUrl());
    }
}
