<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Catalog\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogSessionMock;

    /**
     * @var \Magento\PageCache\Model\DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $depersonalizeCheckerMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayout;

    protected function setUp()
    {
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->catalogSessionMock = $this->getMock('Magento\Catalog\Model\Session',
            ['clearStorage'],
            [],
            '',
            false
        );
        $this->resultLayout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->depersonalizeCheckerMock = $this->getMock(
            'Magento\PageCache\Model\DepersonalizeChecker',
            [],
            [],
            '',
            false
        );

        $this->plugin = (new ObjectManager($this))->getObject(
            'Magento\Catalog\Model\Layout\DepersonalizePlugin',
            ['catalogSession' => $this->catalogSessionMock, 'depersonalizeChecker' => $this->depersonalizeCheckerMock]
        );
    }

    public function testAfterGenerateXml()
    {
        $this->catalogSessionMock->expects($this->once())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $this->resultLayout);
        $this->assertEquals($this->resultLayout, $actualResult);
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        $this->catalogSessionMock->expects($this->never())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $this->resultLayout);
        $this->assertEquals($this->resultLayout, $actualResult);
    }
}
