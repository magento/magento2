<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class ProxyGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scanner\XmlScanner | \PHPUnit\Framework\MockObject\MockObject
     */
    private $proxyScannerMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner | \PHPUnit\Framework\MockObject\MockObject
     */
    private $configurationScannerMock;

    /**
     * @var \Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator
     */
    private $model;

    protected function setUp(): void
    {
        $this->proxyScannerMock = $this->getMockBuilder(\Magento\Setup\Module\Di\Code\Scanner\XmlScanner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationScannerMock = $this->getMockBuilder(
            \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner::class
        )->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator::class,
            [
                'proxyScanner' => $this->proxyScannerMock,
                'configurationScanner' => $this->configurationScannerMock,
            ]
        );
    }

    public function testDoOperation()
    {
        $files = ['file1', 'file2'];
        $this->configurationScannerMock->expects($this->once())
            ->method('scan')
            ->with('di.xml')
            ->willReturn($files);
        $this->proxyScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files)
            ->willReturn([]);

        $this->model->doOperation();
    }
}
