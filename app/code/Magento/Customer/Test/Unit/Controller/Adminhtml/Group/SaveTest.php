<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Group;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Controller\Adminhtml\Group\Save;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /** @var Save */
    protected $controller;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupRepositoryMock;

    /** @var GroupInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupInterfaceFactoryMock;

    /** @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $forwardFactoryMock;

    /** @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageFactoryMock;

    /** @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectProcessorMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectFactory;

    /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var \Magento\Customer\Api\Data\GroupInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerGroup;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultForward;

    /** @var \Magento\Customer\Api\Data\GroupInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $group;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->getMockForAbstractClass();
        $this->groupRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->groupInterfaceFactoryMock = $this->getMockBuilder(GroupInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())->method('isPost')->willReturn(true);
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->group = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->getMockForAbstractClass();
        $this->session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
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
            ->method('addSuccess')
            ->with(__('You saved the customer group.'));
        $exception = new \Exception('Exception');
        $this->resultRedirect->expects($this->at(0))
            ->method('setPath')
            ->with('customer/group')
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('Exception');
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->group, \Magento\Customer\Api\Data\GroupInterface::class)
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
