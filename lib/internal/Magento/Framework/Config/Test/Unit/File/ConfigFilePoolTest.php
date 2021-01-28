<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit\File;

use Magento\Framework\Config\File\ConfigFilePool;

class ConfigFilePoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Config\File\ConfigFilePool
     */
    private $configFilePool;

    protected function setUp(): void
    {
        $newPath = [
            'new_key' => 'new_config.php'
        ];
        $this->configFilePool = new ConfigFilePool($newPath);
    }

    public function testGetPaths()
    {
        $expected['new_key'] = 'new_config.php';
        $expected[ConfigFilePool::APP_CONFIG] = 'config.php';
        $expected[ConfigFilePool::APP_ENV] = 'env.php';

        $this->assertEquals($expected, $this->configFilePool->getPaths());
    }

    public function testGetPath()
    {
        $expected = 'config.php';
        $this->assertEquals($expected, $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG));
    }

    /**
     */
    public function testGetPathException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File config key does not exist.');

        $fileKey = 'not_existing';
        $this->configFilePool->getPath($fileKey);
    }
}
