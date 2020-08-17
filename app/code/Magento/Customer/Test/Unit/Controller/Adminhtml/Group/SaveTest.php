<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Controller\Adminhtml\Group\Save;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends TestCase
{
    /** @var Save */
    protected $controller;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var GroupRepositoryInterface|MockObject */
    protected $groupRepositoryMock;

    /** @var GroupInterfaceFactory|MockObject */
    protected $groupInterfaceFactoryMock;

    /** @var ForwardFactory|MockObject */
    protected $forwardFactoryMock;

    /** @var PageFactory|MockObject */
    protected $pageFactoryMock;

    /** @var DataObjectProcessor|MockObject */
    protected $dataObjectProcessorMock;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var RedirectFactory|MockObject */
    protected $resultRedirectFactory;

    /** @var Redirect|MockObject */
    protected $resultRedirect;

    /** @var GroupInterface|MockObject */
    protected $customerGroup;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    /** @var Forward|MockObject */
    protected $resultForward;

    /** @var GroupInterface|MockObject */
    protected $group;

    /** @var Session|MockObject */
    protected $session;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMockForAbstractClass();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->groupInterfaceFactoryMock = $this->getMockBuilder(GroupInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->group = $this->getMockBuilder(GroupInterface::class)
            ->getMockForAbstractClass();
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomerGroupData'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->contextMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);

        $this->controller = new Save(
            $this->contextMock,
            $this->registryMock,
            $this->groupRepositoryMock,
            $this->groupInterfaceFactoryMock,
            $this->forwardFactoryMock,
            $this->pageFactoryMock,
            $this->dataObjectProcessorMock
        );
    }

    public function testExecuteWithTaxClassAndException()
    {
        $taxClass = '3';
        $groupId = 0;
        $code = 'NOT LOGGED IN';

        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                ['tax_class'],
                ['id'],
                ['code']
            )
            ->willReturnOnConsecutiveCalls($taxClass, $groupId, null);
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($this->group);
        $this->group->expects($this->once())
            ->method('getCode')
            ->willReturn($code);
        $this->group->expects($this->once())
            ->method('setCode')
            ->with($code);
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with($taxClass);
        $this->groupRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->group);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer group.'));
        $exception = new \Exception('Exception');
        $this->resultRedirect->expects($this->at(0))
            ->method('setPath')
            ->with('customer/group')
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Exception');
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->group, GroupInterface::class)
            ->willReturn(['code' => $code]);
        $this->session->expects($this->once())
            ->method('setCustomerGroupData')
            ->with(['customer_group_code' => $code]);
        $this->resultRedirect->expects($this->at(1))
            ->method('setPath')
            ->with('customer/group/edit', ['id' => $groupId]);
        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteWithoutTaxClass()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('tax_class')
            ->willReturn(null);
        $this->forwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('new')
            ->willReturnSelf();
        $this->assertSame($this->resultForward, $this->controller->execute());
    }
}
