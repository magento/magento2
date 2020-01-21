<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;
use Magento\Persistent\Model\Layout\DepersonalizePlugin;
use Magento\Persistent\Model\Session as PersistentSession;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Tests \Magento\Persistent\Model\Layout\DepersonalizePlugin.
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var PersistentSession|PHPUnit_Framework_MockObject_MockObject
     */
    private $persistentSessionMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DepersonalizePlugin
     */
    private $plugin;

    /**
     * @var DepersonalizeChecker|PHPUnit_Framework_MockObject_MockObject
     */
    private $depersonalizeCheckerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->persistentSessionMock = $this->createPartialMock(PersistentSession::class, ['setCustomerId']);
        $this->depersonalizeCheckerMock = $this->createMock(DepersonalizeChecker::class);

        $this->plugin = $this->objectManager->getObject(
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
        /** @var LayoutInterface|PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockForAbstractClass(LayoutInterface::class);

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('setCustomerId')->with(null);

        $this->plugin->afterGenerateElements($subjectMock);
    }

    /**
     * Test afterGenerateElements method when depersonalization is not needed.
     *
     * @return void
     */
    public function testAfterGenerateElementsNoDepersonalize(): void
    {
        /** @var LayoutInterface|PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockForAbstractClass(LayoutInterface::class);

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->persistentSessionMock->expects($this->never())->method('setCustomerId');

        $this->plugin->afterGenerateElements($subjectMock);
    }
}
