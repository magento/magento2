<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class RepositoryGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scanner\RepositoryScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryScannerMock;

    /**
     * @var ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScannerMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configurationScannerMock;

    /**
     * @var \Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator
     */
    private $model;

    protected function setUp()
    {
        $this->repositoryScannerMock =
            $this->getMockBuilder(\Magento\Setup\Module\Di\Code\Scanner\RepositoryScanner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->classesScannerMock = $this->getMockBuilder(\Magento\Setup\Module\Di\Code\Reader\ClassesScanner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationScannerMock = $this->getMockBuilder(
            \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner::class
        )->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator::class,
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
