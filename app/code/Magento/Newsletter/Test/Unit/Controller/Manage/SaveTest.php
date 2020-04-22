<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Controller\Manage;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Controller\Manage;
use Magento\Newsletter\Controller\Manage\Save;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\Newsletter\Controller\Manage\Save
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var Manage
     */
    private $action;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->formKeyValidatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock =
            $this->getMockBuilder(CustomerRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new ObjectManager($this);

        $this->action = $objectManager->getObject(
            Save::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'customerSession' => $this->customerSessionMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );
    }

    public function testSaveActionInvalidFormKey()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(false));
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->never())
            ->method('addError');
        $this->action->execute();
    }

    public function testSaveActionNoCustomerInSession()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(null));
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Something went wrong while saving your subscription.');
        $this->action->execute();
    }

    public function testSaveActionWithException()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(1));
        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->will(
                $this->throwException(
                    new NoSuchEntityException(
                        __(
                            'No such entity with %fieldName = %fieldValue',
                            ['fieldName' => 'customerId', 'value' => 'value']
                        )
                    )
                )
            );
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Something went wrong while saving your subscription.');
        $this->action->execute();
    }
}
