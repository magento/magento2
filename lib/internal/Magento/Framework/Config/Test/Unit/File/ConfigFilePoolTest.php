<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\File;

use Magento\Framework\Config\File\ConfigFilePool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigFilePoolTest extends TestCase
{
    /**
     * @var MockObject|ConfigFilePool
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

    public function testGetPathException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('File config key does not exist.');
        $fileKey = 'not_existing';
        $this->configFilePool->getPath($fileKey);
    }
}
