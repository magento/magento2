<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Api\Data\GroupExtension;
use Magento\Customer\Api\Data\GroupExtensionInterfaceFactory;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends TestCase
{
    use MockCreationTrait;

    /** @var Save */
    private $controller;

    /** @var Context|MockObject */
    private $contextMock;

    /** @var Registry|MockObject */
    private $registryMock;

    /** @var GroupRepositoryInterface|MockObject */
    private $groupRepositoryMock;

    /** @var GroupInterfaceFactory|MockObject */
    private $groupInterfaceFactoryMock;

    /** @var ForwardFactory|MockObject */
    private $forwardFactoryMock;

    /** @var PageFactory|MockObject */
    private $pageFactoryMock;

    /** @var DataObjectProcessor|MockObject */
    private $dataObjectProcessorMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;

    /** @var RedirectFactory|MockObject */
    private $resultRedirectFactoryMock;

    /** @var Redirect|MockObject */
    private $resultRedirectMock;

    /** @var ManagerInterface|MockObject */
    private $messageManagerMock;

    /** @var Forward|MockObject */
    private $resultForwardMock;

    /** @var GroupInterface|MockObject */
    private $groupMock;

    /** @var Session|MockObject */
    private $sessionMock;

    /** @var GroupExtensionInterfaceFactory|MockObject $groupExtensionFactoryMock */
    private $groupExtensionFactoryMock;

    /** @var GroupExtension|MockObject */
    private $groupExtensionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->createMock(Registry::class);
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->groupInterfaceFactoryMock = $this->getMockBuilder(GroupInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupMock = $this->createMock(GroupInterface::class);
        $this->sessionMock = $this->createPartialMockWithReflection(
            Session::class,
            ['setCustomerGroupData']
        );
        $this->groupExtensionFactoryMock = $this->createPartialMock(
            GroupExtensionInterfaceFactory::class,
            ['create']
        );
        $this->groupExtensionMock = $this->createPartialMockWithReflection(
            GroupExtension::class,
            ['setExcludeWebsiteIds']
        );

        $this->contextMock->expects(self::once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects(self::once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects(self::once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->expects(self::once())
            ->method('getSession')
            ->willReturn($this->sessionMock);

        $this->controller = new Save(
            $this->contextMock,
            $this->registryMock,
            $this->groupRepositoryMock,
            $this->groupInterfaceFactoryMock,
            $this->forwardFactoryMock,
            $this->pageFactoryMock,
            $this->dataObjectProcessorMock,
            $this->groupExtensionFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithTaxClassAndException(): void
    {
        $taxClass = '3';
        $groupId = 0;
        $code = 'NOT LOGGED IN';

        $this->requestMock->method('getParam')
            ->willReturnMap(
                [
                    ['tax_class', null, $taxClass],
                    ['id', null, $groupId],
                    ['code', null, null],
                    ['customer_group_excluded_websites', null, '']
                ]
            );
        $this->groupExtensionFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->groupExtensionMock);
        $this->groupExtensionMock->expects(self::once())
            ->method('setExcludeWebsiteIds')
            ->with([])
            ->willReturnSelf();
        $this->groupMock->expects(self::once())
            ->method('setExtensionAttributes')
            ->with($this->groupExtensionMock)
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($groupId)
            ->willReturn($this->groupMock);
        $this->groupMock->expects(self::once())
            ->method('getCode')
            ->willReturn($code);
        $this->groupMock->expects(self::once())
            ->method('setCode')
            ->with($code);
        $this->groupMock->expects(self::once())
            ->method('setTaxClassId')
            ->with($taxClass);
        $this->groupRepositoryMock->expects(self::once())
            ->method('save')
            ->with($this->groupMock);
        $this->messageManagerMock->expects(self::once())
            ->method('addSuccessMessage')
            ->with(__('You saved the customer group.'));
        $this->messageManagerMock->expects(self::once())
            ->method('addErrorMessage')
            ->with('Exception');
        $this->dataObjectProcessorMock->expects(self::once())
            ->method('buildOutputDataArray')
            ->with($this->groupMock, GroupInterface::class)
            ->willReturn(['code' => $code]);
        $this->sessionMock->expects(self::once())
            ->method('setCustomerGroupData')
            ->with(['customer_group_code' => $code]);
        $exception = new \Exception('Exception');
        $this->resultRedirectMock
            ->method('setPath')
            ->willReturnCallback(function ($arg1, $arg2) use ($exception, $groupId) {
                if ($arg1 == 'customer/group') {
                    throw $exception;
                } elseif ($arg1 == 'customer/group/edit' && $arg2 == ['id' => $groupId]) {
                    return null;
                }
            });

        self::assertSame($this->resultRedirectMock, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithoutTaxClass(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getParam')
            ->with('tax_class')
            ->willReturn(null);
        $this->forwardFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects(self::once())
            ->method('forward')
            ->with('new')
            ->willReturnSelf();
        self::assertSame($this->resultForwardMock, $this->controller->execute());
    }
}
