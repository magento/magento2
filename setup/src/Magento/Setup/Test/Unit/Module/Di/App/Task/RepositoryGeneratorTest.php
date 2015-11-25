<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class RepositoryGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scanner\DirectoryScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryScannerMock;

    /**
     * @var Scanner\RepositoryScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryScannerMock;

    /**
     * @var ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScannerMock;

    protected function setUp()
    {
        $this->directoryScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->repositoryScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\RepositoryScanner')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->classesScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Reader\ClassesScanner')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider wrongDataDataProvider
     */
    public function testDoOperationEmptyData($wrongData)
    {
        $model = new RepositoryGenerator(
            $this->directoryScannerMock,
            $this->classesScannerMock,
            $this->repositoryScannerMock,
            $wrongData
        );

        $this->assertNull($model->doOperation());
    }

    /**
     * @return array
     */
    public function wrongDataDataProvider()
    {
        return [
            [[]],
            [['filePatterns' => ['php' => '*.php']]],
            [['path' => 'path']]
        ];
    }

    public function testDoOperationEmptyRepositories()
    {
        $data = [
            'paths' => ['path/to/app'],
            'filePatterns' => ['di' => 'di.xml'],
            'excludePatterns' => ['/\/Test\//'],
        ];
        $files = ['di' => []];
        $model = new RepositoryGenerator(
            $this->directoryScannerMock,
            $this->classesScannerMock,
            $this->repositoryScannerMock,
            $data
        );

        $this->classesScannerMock->expects($this->once())
            ->method('getList')
            ->with($data['paths'][0]);
        $this->directoryScannerMock->expects($this->once())
            ->method('scan')
            ->with(
                $data['paths'][0],
                $data['filePatterns']
            )->willReturn($files);
        $this->repositoryScannerMock->expects($this->once())
            ->method('setUseAutoload')
            ->with(false);
        $this->repositoryScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files['di'])
            ->willReturn([]);

        $this->assertEmpty($model->doOperation());
    }
}
