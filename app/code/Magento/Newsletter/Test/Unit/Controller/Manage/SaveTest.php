<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Newsletter\Test\Unit\Controller\Manage;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Controller\Manage
     */
    private $action;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->formKeyValidatorMock = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock =
            $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->action = $objectManager->getObject('Magento\Newsletter\Controller\Manage\Save', [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'customerSession' => $this->customerSessionMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'customerRepository' => $this->customerRepositoryMock
            ]);
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
            ->will($this->throwException(
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
