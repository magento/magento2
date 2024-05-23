<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Layout;

use Magento\Checkout\Model\Layout\DepersonalizePlugin;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Checkout\Model\Layout\DepersonalizePlugin class.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var DepersonalizeChecker|MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->checkoutSessionMock = $this->createPartialMock(
            CheckoutSession::class,
            ['clearStorage', 'setQuoteId', 'getQuoteId']
        );
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
                'checkoutSession' => $this->checkoutSessionMock,
            ]
        );
    }

    /**
     * Test afterGenerateElements method when depersonalization is needed.
     *
     * @return void
     */
    public function testAfterGenerateElements(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('clearStorage')
            ->willReturnSelf();
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('setQuoteId')
            ->willReturn(1);

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
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('clearStorage')
            ->willReturnSelf();
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('setQuoteId')
            ->willReturn(1);

        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Test beforeGenerateElements method when depersonalization is needed.
     *
     * @return void
     */
    public function testBeforeGenerateXml(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('getQuoteId')
            ->willReturn(1);

        $this->assertEmpty($this->plugin->beforeGenerateXml($this->layoutMock));
    }

    /**
     * Test beforeGenerateElements method when depersonalization is not needed.
     *
     * @return void
     */
    public function testBeforeGenerateXmlNoDepersonalize(): void
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('getQuoteId')
            ->willReturn(1);

        $this->assertEmpty($this->plugin->beforeGenerateXml($this->layoutMock));
    }
}
