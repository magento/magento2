<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\Config;

/**
 * Converter test
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Import\Config\Converter
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
        $this->moduleManager = $this->createPartialMock(\Magento\Framework\Module\Manager::class, ['isOutputEnabled']);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\ImportExport\Model\Import\Config\Converter::class,
            [
                'moduleManager' => $this->moduleManager
            ]
        );
    }

    public function testConvert()
    {
        $testDom = $this->filePath . 'import.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include $this->filePath . 'import.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(true);
        $this->assertEquals($expectedArray, $this->model->convert($dom));
    }

    public function testConvertWithDisabledModules()
    {
        $testDom = $this->filePath . 'import.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $notExpectedArray = include $this->filePath . 'import.php';
        $this->moduleManager->expects($this->any())->method('isOutputEnabled')->willReturn(false);
        $this->assertNotEquals($notExpectedArray, $this->model->convert($dom));
    }
}
