<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\FilterManager;

use Magento\Framework\Filter\Factory;
use Magento\Framework\Filter\FilterManager\Config;
use Magento\Framework\Filter\LaminasFactory;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_config;

    protected function setUp(): void
    {
        $this->_config = new Config(['test' => 'test']);
    }

    public function testGetFactories()
    {
        $expectedConfig = [
            'test' => 'test', Factory::class, LaminasFactory::class,
        ];
        $this->assertEquals($expectedConfig, $this->_config->getFactories());
    }
}
