<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Locks;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Controller\Adminhtml\Locks\Unlock;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Controller\Adminhtml\Locks\Unlock testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnlockTest extends TestCase
{
    /**
     * @var Context
     */
    protected $contextMock;

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authenticationMock;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * @var RequestInterface
     */
    protected $requestMock;

    /**
     * @var ManagerInterface
     */
    protected $messageManagerMock;

    /**
     * @var ResultFactory
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect
     */
    protected $redirectMock;

    /**
     * @var Customer
     */
    protected $customerDataMock;

    /**
     * @var  Unlock
     */
    protected $controller;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->onlyMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->resultFactoryMock = $this->createPartialMock(
            ResultFactory::class,
            ['create']
        );
        $this->redirectMock = $this->createPartialMock(Redirect::class, ['setPath']);
        $this->customerDataMock = $this->createMock(Customer::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getObjectManager', 'getResultFactory', 'getMessageManager', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        $this->controller = $this->objectManager->getObject(
            Unlock::class,
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
            ->willReturn($customerId);
        $this->authenticationMock->expects($this->once())->method('unlock')->with($customerId);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('customer/index/edit'))
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $customerId = 1;
        $phrase = new Phrase('some error');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('customer_id'))
            ->willReturn($customerId);
        $this->authenticationMock->expects($this->once())
            ->method('unlock')
            ->with($customerId)
            ->willThrowException(new \Exception((string)$phrase));
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');
        $this->controller->execute();
    }
}
