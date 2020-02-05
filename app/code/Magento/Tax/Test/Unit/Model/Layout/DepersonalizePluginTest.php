<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Layout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\Tax\Model\Layout\DepersonalizePlugin;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Tax\Model\Layout\DepersonalizePlugin.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerSessionMock = $this->createPartialMock(
            CustomerSession::class,
            [
                'getDefaultTaxBillingAddress',
                'getDefaultTaxShippingAddress',
                'getCustomerTaxClassId',
                'setDefaultTaxBillingAddress',
                'setDefaultTaxShippingAddress',
                'setCustomerTaxClassId'
            ]
        );
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'customerSession' => $this->customerSessionMock,
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
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
        $this->customerSessionMock->expects($this->once())->method('getDefaultTaxBillingAddress');
        $this->customerSessionMock->expects($this->once())->method('getDefaultTaxShippingAddress');
        $this->customerSessionMock->expects($this->once())->method('getCustomerTaxClassId');
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
        $this->customerSessionMock->expects($this->never())->method('getDefaultTaxBillingAddress');
        $this->customerSessionMock->expects($this->never())->method('getDefaultTaxShippingAddress');
        $this->customerSessionMock->expects($this->never())->method('getCustomerTaxClassId');
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
        $this->customerSessionMock->expects($this->once())->method('setDefaultTaxBillingAddress');
        $this->customerSessionMock->expects($this->once())->method('setDefaultTaxShippingAddress');
        $this->customerSessionMock->expects($this->once())->method('setCustomerTaxClassId');
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Test afterGenerateElements method when depersonalization is not needed
     * @return void
     */
    public function testAfterGenerateElementsNoDepersonalize(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->customerSessionMock->expects($this->never())->method('setDefaultTaxBillingAddress');
        $this->customerSessionMock->expects($this->never())->method('setDefaultTaxShippingAddress');
        $this->customerSessionMock->expects($this->never())->method('setCustomerTaxClassId');
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Test beforeGenerateXml and afterGenerateElements methods.
     *
     * @return void
     */
    public function testBeforeAndAfter(): void
    {
        $defaultTaxBillingAddress = [];
        $defaultTaxShippingAddress = [];
        $customerTaxClassId = 1;

        $this->depersonalizeCheckerMock->expects($this->exactly(2))
            ->method('checkIfDepersonalize')
            ->willReturn(true);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxBillingAddress')
            ->willReturn($defaultTaxBillingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxShippingAddress')
            ->willReturn($defaultTaxShippingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);

        $this->plugin->beforeGenerateXml($this->layoutMock);

        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxBillingAddress')
            ->with($defaultTaxBillingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('setDefaultTaxShippingAddress')
            ->with($defaultTaxShippingAddress);
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerTaxClassId')
            ->with($customerTaxClassId);

        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
