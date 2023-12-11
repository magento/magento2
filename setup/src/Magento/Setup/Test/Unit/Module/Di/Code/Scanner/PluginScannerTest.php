<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\PluginScanner;
use PHPUnit\Framework\TestCase;

class PluginScannerTest extends TestCase
{
    /**
     * @var PluginScanner
     */
    private $model;

    /**
     * @var string[]
     */
    private $testFiles;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->model = new PluginScanner();
        $testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->testFiles = [
            $testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $testDir . '/app/etc/di/config.xml',
        ];
    }

    protected function tearDown(): void
    {
        unset($this->model);
    }

    public function testCollectEntities()
    {
        $actual = $this->model->collectEntities($this->testFiles);
        $expected = [\Magento\Framework\App\Cache\TagPlugin::class, \Magento\Store\Model\Action\Plugin::class];
        $this->assertEquals($expected, $actual);
    }
}
