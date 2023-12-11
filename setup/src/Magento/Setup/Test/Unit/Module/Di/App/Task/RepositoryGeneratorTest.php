<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner;
use Magento\Setup\Module\Di\Code\Scanner\RepositoryScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RepositoryGeneratorTest extends TestCase
{
    /**
     * @var Scanner\RepositoryScanner|MockObject
     */
    private $repositoryScannerMock;

    /**
     * @var ClassesScanner|MockObject
     */
    private $classesScannerMock;

    /**
     * @var ConfigurationScanner|MockObject
     */
    private $configurationScannerMock;

    /**
     * @var RepositoryGenerator
     */
    private $model;

    protected function setUp(): void
    {
        $this->repositoryScannerMock =
            $this->getMockBuilder(RepositoryScanner::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->classesScannerMock = $this->getMockBuilder(ClassesScanner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationScannerMock = $this->getMockBuilder(
            ConfigurationScanner::class
        )->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            RepositoryGenerator::class,
            [
                'repositoryScanner' => $this->repositoryScannerMock,
                'classesScanner' => $this->classesScannerMock,
                'configurationScanner' => $this->configurationScannerMock,
                'data' => ['paths' => ['path/to/app']]
            ]
        );
    }

    public function testDoOperation()
    {
        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with('path/to/app');
        $this->repositoryScannerMock->expects($this->once())
            ->method('setUseAutoload')
            ->with(false);
        $files = ['file1', 'file2'];
        $this->configurationScannerMock->expects($this->once())
            ->method('scan')
            ->with('di.xml')
            ->willReturn($files);
        $this->repositoryScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files)
            ->willReturn([]);

        $this->model->doOperation();
    }
}
