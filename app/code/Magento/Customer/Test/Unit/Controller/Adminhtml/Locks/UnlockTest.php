<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Locks;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\Exception;

/**
 * Test class for \Magento\Customer\Controller\Adminhtml\Locks\Unlock testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * Account manager
     *
     * @var \Magento\Customer\Helper\AccountManagement
     */
    protected $accountManagementHelperMock;

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
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $customerRepositoryMock;

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
        $this->contextMock = $this->getMockBuilder('\Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagementHelperMock = $this->getMock(
            'Magento\Customer\Helper\AccountManagement',
            ['processUnlockData'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->resultFactoryMock = $this->getMock(
            'Magento\Framework\Controller\ResultFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->redirectMock = $this->getMock(
            'Magento\Backend\Model\View\Result\Redirect',
            ['setPath'],
            [],
            '',
            false
        );
        $this->customerDataMock = $this->getMock(
            'Magento\Customer\Model\Data\Customer',
            [],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->setMethods(['getObjectManager', 'getResultFactory', 'getMessageManager', 'getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->getMock(
            'Magento\Customer\Model\ResourceModel\CustomerRepository',
            ['getById', 'save'],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        $this->controller = $this->objectManager->getObject(
            '\Magento\Customer\Controller\Adminhtml\Locks\Unlock',
            [
                'context' => $this->contextMock,
                'accountManagementHelper' => $this->accountManagementHelperMock,
                'customerRepository' => $this->customerRepositoryMock
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
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerDataMock);
        $this->accountManagementHelperMock->expects($this->once())->method('processUnlockData')->with($customerId);
        $this->customerRepositoryMock->expects($this->once())->method('save')->with($this->customerDataMock);
        $this->messageManagerMock->expects($this->once())->method('addSuccess');
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with($this->equalTo('customer/index/edit'))
            ->willReturnSelf();
        $this->assertInstanceOf('\Magento\Backend\Model\View\Result\Redirect', $this->controller->execute());
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
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerDataMock);
        $this->accountManagementHelperMock->expects($this->once())
            ->method('processUnlockData')
            ->with($customerId)
            ->willThrowException(new \Exception($phrase));
        $this->messageManagerMock->expects($this->once())->method('addError');
        $this->controller->execute();
    }
}
