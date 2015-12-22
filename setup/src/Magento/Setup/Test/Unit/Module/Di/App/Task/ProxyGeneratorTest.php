<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\ProxyGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

class ProxyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scanner\DirectoryScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryScannerMock;

    /**
     * @var Scanner\XmlScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $proxyScannerMock;

    protected function setUp()
    {
        $this->directoryScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner')
            ->disableOriginalConstructor()
            ->getMock();
        $this->proxyScannerMock = $this->getMockBuilder('Magento\Setup\Module\Di\Code\Scanner\XmlScanner')
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
        $model = new ProxyGenerator(
            $this->directoryScannerMock,
            $this->proxyScannerMock,
            $data
        );

        $this->directoryScannerMock->expects($this->never())
            ->method('scan');
        $this->proxyScannerMock->expects($this->never())
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
            'filePatterns' => ['di' => 'di.xml'],
            'excludePatterns' => ['/\/Test\//'],
        ];
        $files = ['di' => []];
        $model = new ProxyGenerator(
            $this->directoryScannerMock,
            $this->proxyScannerMock,
            $data
        );

        $this->directoryScannerMock->expects($this->once())
            ->method('scan')
            ->with(
                $data['paths'][0],
                $data['filePatterns']
            )->willReturn($files);
        $this->proxyScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files['di'])
            ->willReturn([]);

        $this->assertEmpty($model->doOperation());
    }
}
