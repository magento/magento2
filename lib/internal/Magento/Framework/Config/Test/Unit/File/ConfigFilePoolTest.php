<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit\File;

use Magento\Framework\Config\File\ConfigFilePool;

class ConfigFilePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Config\File\ConfigFilePool
     */
    private $configFilePool;

    protected function setUp()
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
     * @expectedException \Exception
     * @expectedExceptionMessage File config key does not exist.
     */
    public function testGetPathException()
    {
        $fileKey = 'not_existing';
        $this->configFilePool->getPath($fileKey);
    }
}
