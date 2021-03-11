<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Layout;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Layout\DepersonalizePlugin;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Visitor as VisitorModel;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\Generic as GenericSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Customer\Model\Layout\DepersonalizePlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @var GenericSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var CustomerSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerFactoryMock;

    /**
     * @var Customer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    /**
     * @var VisitorModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $visitorMock;

    /**
     * @var DepersonalizeChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->sessionMock = $this->createPartialMock(
            GenericSession::class,
            ['clearStorage', 'setData', 'getData']
        );
        $this->customerSessionMock = $this->createPartialMock(
            CustomerSession::class,
            ['getCustomerGroupId', 'setCustomerGroupId', 'clearStorage', 'setCustomer']
        );
        $this->customerFactoryMock = $this->createPartialMock(CustomerFactory::class, ['create']);
        $this->customerMock = $this->createPartialMock(
            Customer::class,
            ['setGroupId', '__wakeup']
        );
        $this->visitorMock = $this->createMock(VisitorModel::class);
        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
                'session' => $this->sessionMock,
                'customerSession' => $this->customerSessionMock,
                'customerFactory' => $this->customerFactoryMock,
                'visitor' => $this->visitorMock,
            ]
        );
    }

    /**
     * Test beforeGenerateXml method when depersonalization is needed.
     *
     * @return void
     */
    public function testBeforeGenerateXml(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId');
        $this->sessionMock
            ->expects($this->once())
            ->method('getData')
            ->with($this->equalTo(FormKey::FORM_KEY));
        $this->plugin->beforeGenerateXml($this->layoutMock);
    }

    /**
     * Test beforeGenerateXml method when depersonalization is not needed.
     *
     * @return void
     */
    public function testBeforeGenerateXmlNoDepersonalize(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->customerSessionMock->expects($this->never())->method('getCustomerGroupId');
        $this->sessionMock
            ->expects($this->never())
            ->method('getData');
        $this->plugin->beforeGenerateXml($this->layoutMock);
    }

    /**
     * Test afterGenerateElements method when depersonalization is needed.
     *
     * @return void
     */
    public function testAfterGenerateElements(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->visitorMock->expects($this->once())->method('setSkipRequestLogging')->with($this->equalTo(true));
        $this->visitorMock->expects($this->once())->method('unsetData');
        $this->sessionMock->expects($this->once())->method('clearStorage');
        $this->customerSessionMock->expects($this->once())->method('clearStorage');
        $this->customerSessionMock->expects($this->once())->method('setCustomerGroupId')->with($this->equalTo(null));
        $this->customerMock->expects($this->once())->method('setGroupId')->with($this->equalTo(null))->willReturnSelf();
        $this->sessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(FormKey::FORM_KEY),
                $this->equalTo(null)
            );
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomer')
            ->with($this->equalTo($this->customerMock));
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Test afterGenerateElements method when depersonalization is not needed.
     *
     * @return void
     */
    public function testAfterGenerateElementsNoDepersonalize(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->visitorMock->expects($this->never())->method('setSkipRequestLogging');
        $this->visitorMock->expects($this->never())->method('unsetData');
        $this->sessionMock->expects($this->never())->method('clearStorage');
        $this->customerSessionMock->expects($this->never())->method('clearStorage');
        $this->customerSessionMock->expects($this->never())->method('setCustomerGroupId');
        $this->customerMock->expects($this->never())->method('setGroupId');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->customerSessionMock->expects($this->never())->method('setCustomer');
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
