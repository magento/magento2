<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\Confirmation;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfirmationTest extends TestCase
{
    /**
     * @var Confirmation
     */
    private $model;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var Url|MockObject
     */
    private $customerUrlMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLoggedIn'])
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequest'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost', 'getParam'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->customerUrlMock = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLoginUrl'])
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

        $resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLayout'])
            ->getMock();

        $this->resultPageFactoryMock->expects($this->once())->method('create')->willReturn($resultPageMock);

        $layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBlock'])
            ->getMock();

        $resultPageMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $blockMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->addMethods(['setEmail', 'setLoginUrl'])
            ->getMock();

        $layoutMock->expects($this->once())->method('getBlock')->with('accountConfirmation')->willReturn($blockMock);

        $blockMock->expects($this->once())->method('setEmail')->willReturnSelf();
        $blockMock->expects($this->once())->method('setLoginUrl')->willReturnSelf();

        $this->model->execute();
    }
}
