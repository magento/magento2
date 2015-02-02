<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Scanner;

class PluginScannerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Di\Code\Scanner\PluginScanner();
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
        $expected = ['Magento\Framework\App\Cache\TagPlugin', 'Magento\Core\Model\Action\Plugin'];
        $this->assertEquals($expected, $actual);
    }
}
