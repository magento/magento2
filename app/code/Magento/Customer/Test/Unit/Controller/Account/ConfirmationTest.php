<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class ConfirmationTest extends TestCase
{
    use MockCreationTrait;

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
        $this->customerSessionMock = $this->createPartialMock(Session::class, ['isLoggedIn']);
        $this->contextMock = $this->createPartialMock(Context::class, ['getRequest']);
        $this->requestMock = $this->createPartialMock(Http::class, ['getPost', 'getParam']);
        
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->resultPageFactoryMock = $this->createPartialMock(PageFactory::class, ['create']);
        $this->customerUrlMock = $this->createPartialMock(Url::class, ['getLoginUrl']);
        
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

        $resultPageMock = $this->createPartialMock(Page::class, ['getLayout']);

        $this->resultPageFactoryMock->expects($this->once())->method('create')->willReturn($resultPageMock);

        $layoutMock = $this->createPartialMock(Layout::class, ['getBlock']);

        $resultPageMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);

        $blockMock = $this->createPartialMockWithReflection(
            Template::class,
            ['setEmail', 'setLoginUrl']
        );

        $layoutMock->expects($this->once())->method('getBlock')->with('accountConfirmation')->willReturn($blockMock);

        $blockMock->expects($this->once())->method('setEmail')->willReturnSelf();
        $blockMock->expects($this->once())->method('setLoginUrl')->willReturnSelf();

        $this->model->execute();
    }
}
