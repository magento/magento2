<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Multishipping\Block\Checkout\Success;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuccessTest extends TestCase
{
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
        $this->sessionMock = $this->createPartialMock(
            SessionManagerInterface::class,
            [
                'getOrderIds',
                'start',
                'writeClose',
                'isSessionExists',
                'getSessionId',
                'getName',
                'setName',
                'destroy',
                'clearStorage',
                'getCookieDomain',
                'getCookiePath',
                'getCookieLifetime',
                'setSessionId',
                'regenerateId',
                'expireSessionCookie',
                'getSessionIdForHost',
                'isValidForHost',
                'isValidForPath',
                '__wakeup'
            ]
        );
        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->contextMock->expects($this->once())->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->contextMock->expects($this->once())
            ->method('getStoreManager')->will($this->returnValue($this->storeManagerMock));
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
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue('Expected Result'));

        $this->assertEquals('Expected Result', $this->model->getContinueUrl());
    }
}
