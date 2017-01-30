<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Controller\Address\Delete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Delete */
    protected $model;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorMock;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressRepositoryMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $address;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectFactory;

    /** @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resultRedirectFactory =
            $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);
        $this->model =  $objectManager->getObject(
            Delete::class,
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'messageManager' => $this->messageManager,
                'customerSession' => $this->sessionMock,
                'formKeyValidator' => $this->validatorMock,
                'addressRepository' => $this->addressRepositoryMock
            ]
        );
    }

    public function testExecute()
    {
        $addressId = 1;
        $customerId = 2;

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($addressId);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($this->address);
        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->address->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->addressRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($addressId);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('You deleted the address.'));
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->model->execute());
    }

    public function testExecuteWithException()
    {
        $addressId = 1;
        $customerId = 2;

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($addressId);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($this->address);
        $this->sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->address->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(34);
        $exception = new \Exception('Exception');
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('We can\'t delete the address right now.'))
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addException')
            ->with($exception, __('We can\'t delete the address right now.'));
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();
        $this->assertSame($this->resultRedirect, $this->model->execute());
    }
}
