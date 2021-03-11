<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Unit\Controller\Manage;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Controller\Manage
     */
    private $action;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepositoryMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->formKeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock =
            $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->action = $objectManager->getObject(
            \Magento\Newsletter\Controller\Manage\Save::class,
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
            ->willReturn(false);
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
            ->willReturn(true);
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(null);
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
            ->willReturn(true);
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);
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
