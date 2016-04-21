<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

class ConfigurationScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\FileResolver | \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolverMock;

    /**
     * @var \Magento\Framework\App\AreaList | \PHPUnit_Framework_MockObject_MockObject
     */
    private $areaListMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner
     */
    private $model;

    protected function setUp()
    {
        $this->fileResolverMock = $this->getMockBuilder('Magento\Framework\App\Config\FileResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->areaListMock = $this->getMockBuilder('Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner',
            [
                'fileResolver' => $this->fileResolverMock,
                'areaList' => $this->areaListMock,
            ]
        );
    }

    public function testScan()
    {
        $codes = ['code1', 'code2'];
        $iteratorMock = $this->getMockBuilder('Magento\Framework\Config\FileIterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn($codes);
        $counts = count($codes) + 2;
        $this->fileResolverMock->expects($this->exactly($counts))
            ->method('get')
            ->willReturn($iteratorMock);
        $files = ['file1' => 'onefile', 'file2' => 'anotherfile'];
        $iteratorMock->expects($this->exactly($counts))
            ->method('toArray')
            ->willReturn($files);
        $this->assertEquals(array_keys($files), $this->model->scan('di.xml'));
    }
}
