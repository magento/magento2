<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address\Delete;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    /** @var Delete */
    protected $model;

    /** @var Context */
    protected $context;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var Validator|MockObject */
    protected $validatorMock;

    /** @var AddressRepositoryInterface|MockObject */
    protected $addressRepositoryMock;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var AddressInterface|MockObject */
    protected $address;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    /** @var RedirectFactory|MockObject */
    protected $resultRedirectFactory;

    /** @var Redirect|MockObject */
    protected $resultRedirect;

    protected function setUp(): void
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->getMockForAbstractClass();
        $addressInterfaceFactoryMock = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $regionInterfaceFactoryMock = $this->getMockBuilder(RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->address = $this->getMockBuilder(AddressInterface::class)
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resultRedirectFactory =
            $this->getMockBuilder(RedirectFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->context = $objectManager->getObject(
            Context::class,
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
            $formFactoryMock,
            $this->addressRepositoryMock,
            $addressInterfaceFactoryMock,
            $regionInterfaceFactoryMock,
            $dataObjectProcessorMock,
            $dataObjectHelperMock,
            $forwardFactoryMock,
            $pageFactoryMock
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
            ->method('addSuccessMessage')
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
            ->method('addErrorMessage')
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
