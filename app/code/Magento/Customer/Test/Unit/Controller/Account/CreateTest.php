<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Account;

class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\Create
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registrationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirectResultMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->registrationMock = $this->createMock(\Magento\Customer\Model\Registration::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->redirectResultMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);

        $this->redirectFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->pageFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->object = $objectManager->getObject(
            \Magento\Customer\Controller\Account\Create::class,
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
