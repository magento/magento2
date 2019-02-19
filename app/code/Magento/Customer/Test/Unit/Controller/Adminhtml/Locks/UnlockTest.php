<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Locks;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Customer\Controller\Adminhtml\Locks\Unlock testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authenticationMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    protected $redirectMock;

    /**
     * @var \Magento\Customer\Model\Data\Customer
     */
    protected $customerDataMock;

    /**
     * @var  \Magento\Customer\Controller\Adminhtml\Locks\Unlock
     */
    protected $controller;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->resultFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->redirectMock = $this->createPartialMock(\Magento\Backend\Model\View\Result\Redirect::class, ['setPath']);
        $this->customerDataMock = $this->createMock(\Magento\Customer\Model\Data\Customer::class);
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getObjectManager', 'getResultFactory', 'getMessageManager', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        $this->controller = $this->objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Locks\Unlock::class,
            [
                'context' => $this->contextMock,
                'authentication' => $this->authenticationMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'))
            ->will($this->returnValue($customerId));
        $this->authenticationMock->expects($this->once())->method('unlock')->with($customerId);
        $this->messageManagerMock->expects($this->once())->method('addSuccess');
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('customer/index/edit'))
            ->willReturnSelf();
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $customerId = 1;
        $phrase = new \Magento\Framework\Phrase('some error');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'))
            ->will($this->returnValue($customerId));
        $this->authenticationMock->expects($this->once())
            ->method('unlock')
            ->with($customerId)
            ->willThrowException(new \Exception($phrase));
        $this->messageManagerMock->expects($this->once())->method('addError');
        $this->controller->execute();
    }
}
