<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\State;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $writer;

    protected function setUp(): void
    {
        $this->config = $this->createMock(DeploymentConfig::class);
        $this->writer =
            $this->getMockBuilder(Writer::class)
                ->addMethods(['update'])
                ->onlyMethods(['saveConfig'])
                ->disableOriginalConstructor()
                ->getMock();
    }

    /**
     * @param string $cacheType
     * @param array $config
     * @param bool $banAll
     * @param bool $expectedIsEnabled
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($cacheType, $config, $banAll, $expectedIsEnabled)
    {
        $model = new State($this->config, $this->writer, $banAll);
        if ($banAll) {
            $this->config->expects($this->never())->method('getConfigData');
        } else {
            $this->config->expects($this->once())->method('getConfigData')->willReturn($config);
        }
        $this->writer->expects($this->never())->method('update');
        $actualIsEnabled = $model->isEnabled($cacheType);
        $this->assertEquals($expectedIsEnabled, $actualIsEnabled);
    }

    /**
     * @return array
     */
    public static function isEnabledDataProvider()
    {
        return [
            'enabled' => [
                'cacheType' => 'cache_type',
                'config' => ['some_type' => false, 'cache_type' => true],
                'banAll' => false,
                'expectedIsEnabled' => true,
            ],
            'disabled' => [
                'cacheType' => 'cache_type',
                'config' => ['some_type' => true, 'cache_type' => false],
                'banAll' => false,
                'expectedIsEnabled' => false,
            ],
            'unknown is disabled' => [
                'cacheType' => 'unknown_cache_type',
                'config' => ['some_type' => true],
                'banAll' => false,
                'expectedIsEnabled' => false,
            ],
            'disabled, when all caches are banned' => [
                'cacheType' => 'cache_type',
                'config' => ['cache_type' => true],
                'banAll' => true,
                'expectedIsEnabled' => false,
            ]
        ];
    }

    public function testSetEnabled()
    {
        $model = new State($this->config, $this->writer);
        $this->assertFalse($model->isEnabled('cache_type'));
        $model->setEnabled('cache_type', true);
        $this->assertTrue($model->isEnabled('cache_type'));
        $model->setEnabled('cache_type', false);
        $this->assertFalse($model->isEnabled('cache_type'));
    }

    public function testPersist()
    {
        $model = new State($this->config, $this->writer);
        $this->config->expects($this->once())->method('getConfigData')->willReturn(['test_cache_type' => true]);
        $configValue = [ConfigFilePool::APP_ENV => ['cache_types' => ['test_cache_type' => true]]];
        $this->writer->expects($this->once())->method('saveConfig')->with($configValue);
        $model->persist();
    }
}
