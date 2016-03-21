<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Export\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\Config\Converter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    protected function setUp()
    {
        $this->filePath = realpath(__DIR__) . '/_files/';
        $this->moduleManager = $this->getMock('Magento\Framework\Module\Manager', ['isOutputEnabled'], [], '', false);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\ImportExport\Model\Export\Config\Converter',
            [
                'moduleManager' => $this->moduleManager
            ]
        );
    }

    public function testConvert()
    {
        $testDom = $this->filePath . 'export.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include $this->filePath . 'export.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(true);
        $this->assertEquals($expectedArray, $this->model->convert($dom));
    }

    public function testConvertWithDisabledModules()
    {
        $testDom = $this->filePath . 'export.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $notExpectedArray = include $this->filePath . 'export.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(false);
        $this->assertNotEquals($notExpectedArray, $this->model->convert($dom));
    }
}
