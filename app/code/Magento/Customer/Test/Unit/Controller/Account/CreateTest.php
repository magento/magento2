<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Controller\Account;

class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\Create
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registrationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->registrationMock = $this->getMock('\Magento\Customer\Model\Registration', [], [], '', false);
        $this->redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface');
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface');
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->redirectResultMock = $this->getMock('Magento\Framework\Controller\Result\Redirect', [], [], '', false);

        $this->redirectFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->resultPageMock = $this->getMock('Magento\Framework\View\Result\Page', [], [], '', false );
        $this->pageFactoryMock = $this->getMock('Magento\Framework\View\Result\PageFactory', [], [], '', false);

        $this->object = $objectManager->getObject('Magento\Customer\Controller\Account\Create',
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
            ->will($this->returnValue(false));

        $this->registrationMock->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(false));

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
            ->will($this->returnValue(false));

        $this->registrationMock->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->redirectMock->expects($this->never())
            ->method('redirect');

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->object->execute();
    }
}
