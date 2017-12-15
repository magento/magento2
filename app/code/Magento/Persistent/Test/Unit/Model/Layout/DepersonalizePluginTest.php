<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Persistent\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * @var \Magento\PageCache\Model\DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $depersonalizeCheckerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->persistentSessionMock = $this->createPartialMock(
            \Magento\Persistent\Model\Session::class,
            ['setCustomerId']
        );

        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->moduleManagerMock = $this->createPartialMock(\Magento\Framework\Module\Manager::class, ['isEnabled']);
        $this->cacheConfigMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['isEnabled']);
        $this->depersonalizeCheckerMock = $this->createMock(\Magento\PageCache\Model\DepersonalizeChecker::class);

        $this->plugin = $this->objectManager->getObject(
            \Magento\Persistent\Model\Layout\DepersonalizePlugin::class,
            [
                'persistentSession' => $this->persistentSessionMock,
                'depersonalizeChecker' => $this->depersonalizeCheckerMock,
            ]
        );
    }

    public function testAfterGenerateXml()
    {
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isCacheable']
        );
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('setCustomerId')->with(null);

        $this->assertEquals($resultMock, $this->plugin->afterGenerateXml($subjectMock, $resultMock));
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isCacheable']
        );
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->persistentSessionMock->expects($this->never())->method('setCustomerId');

        $this->assertEquals($resultMock, $this->plugin->afterGenerateXml($subjectMock, $resultMock));
    }
}
