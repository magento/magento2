<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Controller\Address\Delete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Delete */
    protected $model;

    /** @var \Magento\Framework\App\Action\Context */
    protected $context;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorMock;

    /** @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactoryMock;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressRepositoryMock;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressInterfaceFactoryMock;

    /** @var \Magento\Customer\Api\Data\RegionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $regionInterfaceFactoryMock;

    /** @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectProcessorMock;

    /** @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectHelperMock;

    /** @var \Magento\Framework\Controller\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $forwardFactoryMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

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
        $this->sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactoryMock = $this->getMockBuilder('Magento\Customer\Model\Metadata\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder('Magento\Customer\Api\AddressRepositoryInterface')
            ->getMockForAbstractClass();
        $this->addressInterfaceFactoryMock = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->regionInterfaceFactoryMock = $this->getMockBuilder('Magento\Customer\Api\Data\RegionInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->context = $objectManager->getObject(
            'Magento\Framework\App\Action\Context',
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $this->resultRedirectFactory,
            ]
        );

        $this->model = new Delete(
            $this->context,
            $this->sessionMock,
            $this->validatorMock,
            $this->formFactoryMock,
            $this->addressRepositoryMock,
            $this->addressInterfaceFactoryMock,
            $this->regionInterfaceFactoryMock,
            $this->dataObjectProcessorMock,
            $this->dataObjectHelperMock,
            $this->forwardFactoryMock,
            $this->pageFactoryMock
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
