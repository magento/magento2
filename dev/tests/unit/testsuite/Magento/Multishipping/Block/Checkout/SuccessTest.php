<?php
/** 
 * 
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace Magento\Multishipping\Block\Checkout;
 
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
        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface', [], [], '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
