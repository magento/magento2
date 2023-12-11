<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Backend\Block\Page\System\Config\Robots\Reset
 */
namespace Magento\Backend\Test\Unit\Block\Page\System\Config\Robots;

use Magento\Backend\Block\Page\System\Config\Robots\Reset;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Original class is deprecated
 */
class ResetTest extends TestCase
{
    /**
     * @var Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $objectHelper = new ObjectManager($this);
        $context = $objectHelper->getObject(
            Context::class,
            ['scopeConfig' => $this->configMock]
        );

        $this->_resetRobotsBlock = new Reset($context, []);
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
