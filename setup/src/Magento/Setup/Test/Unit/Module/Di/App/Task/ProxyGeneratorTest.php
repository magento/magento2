<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner;
use Magento\Setup\Module\Di\Code\Scanner\XmlScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProxyGeneratorTest extends TestCase
{
    /**
     * @var Scanner\XmlScanner|MockObject
     */
    private $proxyScannerMock;

    /**
     * @var ConfigurationScanner|MockObject
     */
    private $configurationScannerMock;

    /**
     * @var ProxyGenerator
     */
    private $model;

    protected function setUp(): void
    {
        $this->proxyScannerMock = $this->getMockBuilder(XmlScanner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationScannerMock = $this->getMockBuilder(
            ConfigurationScanner::class
        )->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            ProxyGenerator::class,
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
