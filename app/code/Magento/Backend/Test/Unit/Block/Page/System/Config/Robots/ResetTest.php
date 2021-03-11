<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Page\System\Config\Robots\Reset
 */
namespace Magento\Backend\Test\Unit\Block\Page\System\Config\Robots;

/**
 * Test for Reset
 * @deprecated no alternative defined
 */
class ResetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Page\System\Config\Robots\Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

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
        $this->configMock->expects($this->once())->method('getValue')->willReturn($expectedInstructions);
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
