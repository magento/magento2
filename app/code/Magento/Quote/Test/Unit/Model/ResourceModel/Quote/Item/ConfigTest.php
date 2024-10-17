<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote\Item;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->configMock->method('getValue')->willReturn('1');
        $this->config = new Config($this->configMock);
    }

    /**
     * @return void
     */
    public function testIsEnabled()
    {
        $this->assertEquals(true, $this->config->isEnabled());
    }

    /**
     * @return void
     */
    public function testIsDisabled()
    {
        $this->setUpForDisabled();
        $this->assertEquals(false, $this->config->isEnabled());
    }

    /**
     * @return void
     */
    private function setUpForDisabled()
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->configMock->method('getValue')->willReturn('0');
        $this->config = new Config($this->configMock);
    }
}
