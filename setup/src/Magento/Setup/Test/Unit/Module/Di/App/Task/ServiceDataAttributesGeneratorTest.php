<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\ServiceDataAttributesGenerator;
use Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class ServiceDataAttributesGeneratorTest
 */
class ServiceDataAttributesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryScannerMock;
    
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceDataAttributesScannerMock;

    protected function setUp()
    {
        $this->directoryScannerMock = $this->getMock(
            'Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner',
            [],
            [],
            '',
            false
        );
        $this->serviceDataAttributesScannerMock = $this->getMock(
            'Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @param $data array
     * @dataProvider doOperationDataProvider
     */
    public function testDoOperationEmptyData($data)
    {
        $model = new ServiceDataAttributesGenerator(
            $this->directoryScannerMock,
            $this->serviceDataAttributesScannerMock,
            $data
        );
        $this->directoryScannerMock->expects($this->never())->method('scan');

        $model->doOperation();
    }

    /**
     * @return array
     */
    public function doOperationDataProvider()
    {
        return [
            [[]],
            [['filePatterns' => ['php' => '*.php']]],
            [['path' => 'path']]
        ];
    }

    public function testDoOperation()
    {
        $data = [
            'paths' => ['path/to/app'],
            'filePatterns' => ['di' => 'di.xml'],
        ];
        $files = ['extension_attributes' => []];
        $model = new ServiceDataAttributesGenerator(
            $this->directoryScannerMock,
            $this->serviceDataAttributesScannerMock,
            $data
        );

        $this->directoryScannerMock->expects($this->once())
            ->method('scan')
            ->with(
                $data['paths'][0],
                $data['filePatterns']
            )->willReturn($files);
        $this->serviceDataAttributesScannerMock->expects($this->once())
            ->method('collectEntities')
            ->with($files['extension_attributes'])
            ->willReturn([]);

        $model->doOperation();
    }
}
