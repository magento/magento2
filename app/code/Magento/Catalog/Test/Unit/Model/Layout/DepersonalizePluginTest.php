<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layout;

use Magento\Catalog\Model\Layout\DepersonalizePlugin;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Catalog\Model\Layout\DepersonalizePlugin.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var CatalogSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $catalogSessionMock;

    /**
     * @var DepersonalizeChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @var LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->catalogSessionMock = $this->createPartialMock(CatalogSession::class, ['clearStorage']);
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'catalogSession' => $this->catalogSessionMock,
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
            ]
        );
    }

    /**
     * Tests afterGenerateElements method when depersonalization is needed.
     *
     * @return void
     */
    public function testAfterGenerateElements(): void
    {
        $this->catalogSessionMock->expects($this->once())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }

    /**
     * Tests afterGenerateElements method when depersonalization is not needed.
     *
     * @return void
     */
    public function testAfterGenerateElementsNoDepersonalize(): void
    {
        $this->catalogSessionMock->expects($this->never())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
