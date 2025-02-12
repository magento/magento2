<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Controller\Account\Create;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    /**
     * @var Create
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $customerSession;

    /**
     * @var MockObject
     */
    protected $registrationMock;

    /**
     * @var MockObject
     */
    protected $redirectMock;

    /**
     * @var MockObject
     */
    protected $response;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $redirectResultMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var PageFactory|MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->customerSession = $this->createMock(Session::class);
        $this->registrationMock = $this->createMock(Registration::class);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectResultMock = $this->createMock(Redirect::class);

        $this->redirectFactoryMock = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->createMock(Page::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);

        $this->object = $objectManager->getObject(
            Create::class,
            [
                'request' => $this->request,
                'response' => $this->response,
                'customerSession' => $this->customerSession,
                'registration' => $this->registrationMock,
                'redirect' => $this->redirectMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
                'resultPageFactory' => $this->pageFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateActionRegistrationDisabled()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registrationMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(false);

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectResultMock);

        $this->redirectResultMock->expects($this->once())
            ->method('setPath')
            ->with('*/*')
            ->willReturnSelf();

        $this->resultPageMock->expects($this->never())
            ->method('getLayout');

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testCreateActionRegistrationEnabled()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->registrationMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);

        $this->redirectMock->expects($this->never())
            ->method('redirect');

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->object->execute();
    }
}
