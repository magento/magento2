<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Page\System\Config\Robots\Reset
 */
namespace Magento\Backend\Test\Unit\Block\Page\System\Config\Robots;

/**
 * Class ResetTest
 * @deprecated 
 * @package Magento\Backend\Test\Unit\Block\Page\System\Config\Robots
 */
class ResetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Page\System\Config\Robots\Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            \Magento\Backend\Block\Template\Context::class,
            ['scopeConfig' => $this->configMock]
        );

        $this->_resetRobotsBlock = new \Magento\Backend\Block\Page\System\Config\Robots\Reset($context, []);
    }

    /**
     * @covers \Magento\Backend\Block\Page\System\Config\Robots\Reset::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $expectedInstructions = 'User-agent: *';
        $this->configMock->expects($this->once())->method('getValue')->will($this->returnValue($expectedInstructions));
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
