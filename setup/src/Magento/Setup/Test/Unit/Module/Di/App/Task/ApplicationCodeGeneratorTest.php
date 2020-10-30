<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner;
use Magento\Setup\Module\Di\Code\Scanner\PhpScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationCodeGeneratorTest extends TestCase
{
    /**
     * @var Scanner\DirectoryScanner|MockObject
     */
    private $directoryScannerMock;

    /**
     * @var Scanner\PhpScanner|MockObject
     */
    private $phpScannerMock;

    /**
     * @var ClassesScanner|MockObject
     */
    private $classesScannerMock;

    protected function setUp(): void
    {
        $this->directoryScannerMock = $this->getMockBuilder(
            DirectoryScanner::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->phpScannerMock = $this->getMockBuilder(PhpScanner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->classesScannerMock = $this->getMockBuilder(ClassesScanner::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $data
     *
     * @dataProvider doOperationWrongDataDataProvider
     */
    public function testDoOperationWrongData($data)
    {
        $model = new ApplicationCodeGenerator(
            $this->classesScannerMock,
            $this->phpScannerMock,
            $this->directoryScannerMock,
            $data
        );

        $this->classesScannerMock->expects($this->never())
            ->method('getList');
        $this->directoryScannerMock->expects($this->never())
            ->method('scan');
        $this->phpScannerMock->expects($this->never())
            ->method('collectEntities');

        $this->assertEmpty($model->doOperation());
    }

    /**
     * @return array
     */
    public function doOperationWrongDataDataProvider()
    {
        return [
            [[]],
            [['filePatterns' => ['php' => '*.php']]],
            [['path' => 'path']],
        ];
    }

    public function testDoOperation()
    {
        $data = [
            'paths' => ['path/to/app'],
            'filePatterns' => ['php' => '.php'],
            'excludePatterns' => ['/\/Test\//']
        ];
        $files = ['php' => []];
        $model = new ApplicationCodeGenerator(
            $this->classesScannerMock,
            $this->phpScannerMock,
            $this->directoryScannerMock,
            $data
        );

        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($data['paths'][0]);
        $this->directoryScannerMock->expects($this->once())
            ->method('scan')
            ->with(
                $data['paths'][0],
                $data['filePatterns'],
                $data['excludePatterns']
            )->willReturn($files);
        $this->phpScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files['php'])
            ->willReturn([]);

        $this->assertEmpty($model->doOperation());
    }
}
