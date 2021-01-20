<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\Confirmation;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfirmationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Confirmation
     */
    private $model;
    
    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerUrlMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoggedIn'])
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPost', 'getParam'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        
        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerUrlMock = $this->getMockBuilder(\Magento\Customer\Model\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoginUrl'])
            ->getMock();
        $this->model = (new ObjectManagerHelper($this))->getObject(
            Confirmation::class,
            [
                'context' => $this->contextMock,
                'customerSession' => $this->customerSessionMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'customerUrl' => $this->customerUrlMock,
            ]
        );
    }

    public function testGetLoginUrl()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        
        $this->requestMock->expects($this->once())->method('getPost')->with('email')->willReturn(null);

        $resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMock();

        $this->resultPageFactoryMock->expects($this->once())->method('create')->willReturn($resultPageMock);

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();

        $resultPageMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setEmail', 'setLoginUrl'])
            ->getMock();

        $layoutMock->expects($this->once())->method('getBlock')->with('accountConfirmation')->willReturn($blockMock);

        $blockMock->expects($this->once())->method('setEmail')->willReturnSelf();
        $blockMock->expects($this->once())->method('setLoginUrl')->willReturnSelf();

        $this->model->execute();
    }
}
