<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner;
use PHPUnit\Framework\TestCase;

class DirectoryScannerTest extends TestCase
{
    /**
     * @var DirectoryScanner
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_testDir;

    protected function setUp(): void
    {
        $this->_model = new DirectoryScanner();
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
    }

    public function testScan()
    {
        $filePatterns = [
            'php' => '/.*\.php$/',
            'etc' => '/\/app\/etc\/.*\.xml$/',
            'config' => '/\/etc\/(config([a-z0-9\.]*)?|adminhtml\/system)\.xml$/',
            'view' => '/\/view\/[a-z0-9A-Z\/\.]*\.xml$/',
            'design' => '/\/app\/design\/[a-z0-9A-Z\/\.]*\.xml$/',
        ];

        $actual = $this->_model->scan($this->_testDir, $filePatterns);
        $expected = [
            'php' => [
                $this->_testDir . '/additional.php',
                $this->_testDir . '/app/bootstrap.php',
                $this->_testDir . '/app/code/Magento/SomeModule/Helper/TestHelper.php',
                $this->_testDir . '/app/code/Magento/SomeModule/Model/TestHelper.php',
            ],
            'config' => [
                $this->_testDir . '/app/code/Magento/SomeModule/etc/adminhtml/system.xml',
                $this->_testDir . '/app/code/Magento/SomeModule/etc/config.xml',
            ],
            'view' => [$this->_testDir . '/app/code/Magento/SomeModule/view/frontend/default.xml'],
            'design' => [$this->_testDir . '/app/design/adminhtml/Magento/backend/layout.xml'],
            'etc' => [$this->_testDir . '/app/etc/additional.xml', $this->_testDir . '/app/etc/config.xml'],
        ];
        $this->assertEquals(sort($expected['php']), sort($actual['php']), 'Incorrect php files list');
        $this->assertEquals(sort($expected['config']), sort($actual['config']), 'Incorrect config files list');
        $this->assertEquals(sort($expected['view']), sort($actual['view']), 'Incorrect view files list');
        $this->assertEquals(sort($expected['design']), sort($actual['design']), 'Incorrect design files list');
        $this->assertEquals(sort($expected['etc']), sort($actual['etc']), 'Incorrect etc files list');
    }
}
