<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer
     */
    private $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    protected function setUp()
    {

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->persistentSessionMock = $this->getMockBuilder(\Magento\Persistent\Helper\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerViewHelperMock = $this->getMockBuilder(\Magento\Customer\Helper\View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Persistent\Helper\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId'])
            ->getMock();
        $this->observer = $objectManagerHelper->getObject(
            \Magento\Persistent\Model\Observer::class,
            [
                'persistentSession' => $this->persistentSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'escaper' => $this->escaperMock,
                'layout' => $this->layoutMock
            ]
        );
    }

    public function testEmulateWelcomeBlock()
    {
        $customerId = 1;
        $customerName = 'Test Customer Name';
        $welcomeMessage =  __('Welcome, %1!', $customerName);
        $customerMock = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWelcome'])
            ->getMock();
        $headerAdditionalBlock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->persistentSessionMock->expects($this->once())->method('getSession')->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)->willReturn($customerMock);
        $this->customerViewHelperMock->expects($this->once())->method('getCustomerName')->willReturn($customerName);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('header.additional')
            ->willReturn($headerAdditionalBlock);
        $block->expects($this->once())->method('setWelcome')->with($welcomeMessage);
        $this->observer->emulateWelcomeBlock($block);
    }
}
