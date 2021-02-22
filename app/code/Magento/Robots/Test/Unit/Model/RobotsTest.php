<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Robots\Model\Robots;
use Magento\Store\Model\ScopeInterface;

class RobotsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Robots
     */
    private $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Robots(
            $this->scopeConfigMock
        );
    }

    /**
     * Check general logic of getData() method
     */
    public function testGetData()
    {
        $customInstructions = 'custom_instructions';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'design/search_engine_robots/custom_instructions',
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn($customInstructions);

        $this->assertEquals($customInstructions, $this->model->getData());
    }
}
