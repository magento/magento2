<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\Persistent\Model\Layout\DepersonalizePlugin;
use Magento\Persistent\Model\Session as PersistentSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Persistent\Model\Layout\DepersonalizePlugin class.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var PersistentSession|MockObject
     */
    private $persistentSessionMock;

    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var DepersonalizeChecker|MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->persistentSessionMock = $this->getMockBuilder(PersistentSession::class)
            ->addMethods(['setCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            DepersonalizePlugin::class,
            [
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
                'persistentSession' => $this->persistentSessionMock,
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
        $this->persistentSessionMock->expects($this->once())->method('setCustomerId')->with(null);

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
        $this->persistentSessionMock->expects($this->never())->method('setCustomerId');

        $this->assertEmpty($this->plugin->afterGenerateElements($this->layoutMock));
    }
}
