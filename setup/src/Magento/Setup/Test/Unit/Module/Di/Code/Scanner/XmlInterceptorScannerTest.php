<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\XmlInterceptorScanner;
use PHPUnit\Framework\TestCase;

class XmlInterceptorScannerTest extends TestCase
{
    /**
     * @var XmlInterceptorScanner
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var array
     */
    protected $_testFiles = [];

    protected function setUp(): void
    {
        $this->_model = new XmlInterceptorScanner();
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
        $this->_testFiles = [
            $this->_testDir . '/app/code/Magento/SomeModule/etc/di.xml',
            $this->_testDir . '/app/etc/di/config.xml',
        ];
    }

    public function testCollectEntities()
    {
        $actual = $this->_model->collectEntities($this->_testFiles);
        $expected = [
            \Magento\Framework\App\Cache\Interceptor::class,
            \Magento\Framework\App\Action\Context\Interceptor::class,
        ];
        $this->assertEquals($expected, $actual);
    }
}
