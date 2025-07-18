<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Config;
use Magento\Framework\App\State;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class NewRelicWrapperTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var State|MockObject
     */
    private $state;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->state = $this->createMock(State::class);
        $this->newRelicWrapper = new NewRelicWrapper($this->config, $this->state);
    }

    public function testGetCurrentAppName()
    {
        $this->config->expects($this->once())
            ->method('isSeparateApps')
            ->willReturn(true);
        $this->config->expects($this->atLeastOnce())
            ->method('getNewRelicAppName')
            ->willReturn('Magento');
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->state->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('cron');
        $this->assertEquals('Magento;Magento_cron', $this->newRelicWrapper->getCurrentAppName());
    }
}
