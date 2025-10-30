<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\Test\Unit\Controller\Login;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\LoginAsCustomerFrontendUi\Controller\Login\Index;
use Magento\Checkout\Model\Session as CheckoutSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\LoginAsCustomerFrontendUi\Controller\Login\Index
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var AuthenticateCustomerBySecretInterface|MockObject
     */
    private $authenticateCustomerBySecretMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultPageMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactoryMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->authenticateCustomerBySecretMock = $this->getMockForAbstractClass(
            AuthenticateCustomerBySecretInterface::class
        );
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getFirstname', 'getLastname'])
            ->getMock();
        $this->redirectMock = $this->createMock(Redirect::class);
        $this->resultPageMock = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getConfig', 'getTitle', 'set'])
            ->getMockForAbstractClass();
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->controller = new Index(
            $this->resultFactoryMock,
            $this->requestMock,
            $this->authenticateCustomerBySecretMock,
            $this->messageManagerMock,
            $this->loggerMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock
        );
    }

    /**
     * Test execute method with existing session
     */
    public function testExecuteWithQuoteId()
    {
        $secret = 'test-secret';
        $firstName = 'John';
        $lastName = 'Doe';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('secret')
            ->willReturn($secret);
        $this->resultFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->checkoutSessionMock->expects($this->once())->method('getQuoteId')->willReturn(123);
        $this->checkoutSessionMock->expects($this->once())->method('clearQuote');
        $this->checkoutSessionMock->expects($this->once())->method('clearStorage');
        $this->mockHelper($secret, $firstName, $lastName);
    }

    /**
     * Test execute method without existing session
     */
    public function testExecuteWithoutQuoteId()
    {
        $secret = 'test-secret';
        $firstName = 'John';
        $lastName = 'Doe';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('secret')
            ->willReturn($secret);
        $this->resultFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($this->resultPageMock);
        $this->checkoutSessionMock->expects($this->once())->method('getQuoteId')->willReturn(false);
        $this->checkoutSessionMock->expects($this->never())->method('clearQuote');
        $this->checkoutSessionMock->expects($this->never())->method('clearStorage');
        $this->mockHelper($secret, $firstName, $lastName);
    }

    /**
     * Test execute method with LocalizedException
     */
    public function testExecuteWithLocalizedException()
    {
        $secret = 'test-secret';
        $exceptionMessage = 'Authentication failed';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('secret')
            ->willReturn($secret);
        $this->checkoutSessionMock->expects($this->once())->method('getQuoteId')->willReturn(false);
        $this->authenticateCustomerBySecretMock->expects($this->once())
            ->method('execute')
            ->with($secret)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('/');
        $result = $this->controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    /**
     * Test execute method with Exception
     */
    public function testExecuteWithGenericException()
    {
        $secret = 'test-secret';
        $exceptionMessage = 'Error';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('secret')
            ->willReturn($secret);
        $this->checkoutSessionMock->expects($this->once())->method('getQuoteId')->willReturn(null);
        $this->authenticateCustomerBySecretMock->expects($this->once())
            ->method('execute')
            ->with($secret)
            ->willThrowException(new \Exception($exceptionMessage));
        $this->loggerMock->expects($this->once())->method('error')->with($exceptionMessage);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Cannot login to account.'));
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())->method('setPath')->with('/');
        $result = $this->controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    /**
     * @param string $secret
     * @param string $firstName
     * @param string $lastName
     * @return void
     */
    private function mockHelper(string $secret, string $firstName, string $lastName): void
    {
        $this->authenticateCustomerBySecretMock->expects($this->once())->method('execute')->with($secret);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getFirstname')->willReturn($firstName);
        $this->customerMock->expects($this->once())->method('getLastname')->willReturn($lastName);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You are logged in as customer: %1', $firstName . ' ' . $lastName));
        $this->resultPageMock->expects($this->once())->method('getConfig')->willReturnSelf();
        $this->resultPageMock->expects($this->once())->method('getTitle')->willReturnSelf();
        $this->resultPageMock->expects($this->once())->method('set')->with(__('You are logged in'));
        $result = $this->controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
    }
}
