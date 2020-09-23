<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\App\Task\Operation\ServiceDataAttributesGenerator;
use Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner;
use Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceDataAttributesGeneratorTest extends TestCase
{
    /**
     * @var ConfigurationScanner|MockObject
     */
    private $configurationScannerMock;

    /**
     * @var ServiceDataAttributesScanner|MockObject
     */
    private $serviceDataAttributesScannerMock;

    /**
     * @var ServiceDataAttributesGenerator
     */
    private $model;

    protected function setUp(): void
    {
        $this->configurationScannerMock = $this->getMockBuilder(
            ConfigurationScanner::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->serviceDataAttributesScannerMock = $this->getMockBuilder(
            ServiceDataAttributesScanner::class
        )->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            ServiceDataAttributesGenerator::class,
            [
                'serviceDataAttributesScanner' => $this->serviceDataAttributesScannerMock,
                'configurationScanner' => $this->configurationScannerMock,
            ]
        );
    }

    public function testDoOperation()
    {
        $files = ['file1', 'file2'];
        $this->configurationScannerMock->expects($this->once())
            ->method('scan')
            ->with('extension_attributes.xml')
            ->willReturn($files);
        $this->serviceDataAttributesScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files)
            ->willReturn([]);

        $this->model->doOperation();
    }
}
