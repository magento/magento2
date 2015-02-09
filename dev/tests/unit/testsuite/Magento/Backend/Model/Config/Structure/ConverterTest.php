<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $factoryMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Mapper\Factory',
            [],
            [],
            '',
            false,
            false
        );

        $mapperMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Mapper\Dependencies',
            [],
            [],
            '',
            false,
            false
        );
        $mapperMock->expects($this->any())->method('map')->will($this->returnArgument(0));
        $factoryMock->expects($this->any())->method('create')->will($this->returnValue($mapperMock));

        $this->_model = new \Magento\Backend\Model\Config\Structure\Converter($factoryMock);
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
