<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

class PluginScannerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->_model = new \Magento\Setup\Module\Di\Code\Scanner\PluginScanner();
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->_testFiles = [
            $this->_testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $this->_testDir . '/app/etc/di/config.xml',
        ];
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testCollectEntities()
    {
        $actual = $this->_model->collectEntities($this->_testFiles);
        $expected = ['Magento\Framework\App\Cache\TagPlugin', 'Magento\Store\Model\Action\Plugin'];
        $this->assertEquals($expected, $actual);
    }
}
