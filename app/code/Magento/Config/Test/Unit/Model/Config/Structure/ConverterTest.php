<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(\Magento\Config\Model\Config\Structure\Mapper\Factory::class);

        $mapperMock = $this->createMock(\Magento\Config\Model\Config\Structure\Mapper\Dependencies::class);
        $mapperMock->expects($this->any())->method('map')->willReturnArgument(0);
        $factoryMock->expects($this->any())->method('create')->willReturn($mapperMock);

        $this->_model = new \Magento\Config\Model\Config\Structure\Converter($factoryMock);
    }

    public function testConvertCorrectlyConvertsConfigStructureToArray()
    {
        $testDom = dirname(dirname(__DIR__)) . '/_files/system_2.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include dirname(dirname(__DIR__)) . '/_files/converted_config.php';
        $this->assertEquals($expectedArray, $this->_model->convert($dom));
    }
}
