<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Multishipping\Block\Checkout\Success;

class SuccessTest extends \PHPUnit_Framework_TestCase
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
        $this->sessionMock = $this->getMock(
            'Magento\Framework\Session\SessionManagerInterface',
            [
                'getOrderIds', 'start', 'writeClose', 'isSessionExists', 'getSessionId', 'getName', 'setName',
                'destroy', 'clearStorage', 'getCookieDomain', 'getCookiePath', 'getCookieLifetime', 'setSessionId',
                'regenerateId', 'expireSessionCookie', 'getSessionIdForHost', 'isValidForHost', 'isValidForPath',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock->expects($this->once())->method('getSession')->will($this->returnValue($this->sessionMock));
        $this->contextMock->expects($this->once())
            ->method('getStoreManager')->will($this->returnValue($this->storeManagerMock));
        $this->model = $objectManager->getObject('Magento\Multishipping\Block\Checkout\Success',
            [
                'context' => $this->contextMock
            ]);
    }

    public function testGetOrderIdsWithoutId()
    {
        $this->sessionMock->expects($this->once())->method('getOrderIds')->with(true)->will($this->returnValue(null));

        $this->assertFalse($this->model->getOrderIds());
    }

    public function testGetOrderIdsWithEmptyIdsArray()
    {
        $this->sessionMock->expects($this->once())->method('getOrderIds')->with(true)->will($this->returnValue([]));

        $this->assertFalse($this->model->getOrderIds());
    }

    public function testGetOrderIds()
    {
        $ids = [100, 102, 103];
        $this->sessionMock->expects($this->once())->method('getOrderIds')->with(true)->will($this->returnValue($ids));

        $this->assertEquals($ids, $this->model->getOrderIds());
    }

    public function testGetContinueUrl()
    {
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue('Expected Result'));

        $this->assertEquals('Expected Result', $this->model->getContinueUrl());
    }
}
