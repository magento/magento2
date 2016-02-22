<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class ApplicationCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scanner\DirectoryScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryScannerMock;

    /**
     * @var Scanner\PhpScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $phpScannerMock;

    /**
     * @var ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScannerMock;

    protected function setUp()
    {
        $this->directoryScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner')
            ->disableOriginalConstructor()
            ->getMock();
        $this->phpScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\PhpScanner')
            ->disableOriginalConstructor()
            ->getMock();
        $this->classesScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Reader\ClassesScanner')
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
