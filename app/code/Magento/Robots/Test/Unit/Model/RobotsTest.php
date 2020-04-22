<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Robots\Model\Robots;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RobotsTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
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
