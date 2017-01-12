<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Convert\Test\Unit;

use \Magento\Framework\Convert\ConvertArray;

class ConvertArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConvertArray
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new ConvertArray();
    }

    public function testAssocToXml()
    {
        $data = ['one' => 1, 'two' => ['three' => 3, 'four' => '4']];
        $result = $this->_model->assocToXml($data);
        $expectedResult = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<_><one>1</one><two><three>3</three><four>4</four></two></_>

XML;
        $this->assertInstanceOf('SimpleXMLElement', $result);
        $this->assertEquals($expectedResult, $result->asXML());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Associative and numeric keys must not be mixed at one level.
     */
    public function testAssocToXmlExceptionByKey()
    {
        $data = [
            'one' => [
                100,
                'two' => 'three',
            ],
        ];
        $this->_model->assocToXml($data);
    }

    /**
     * @param array $array
     * @param string $rootName
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider assocToXmlExceptionDataProvider
     */
    public function testAssocToXmlException($array, $rootName = '_')
    {
        $this->_model->assocToXml($array, $rootName);
    }

    public function testToFlatArray()
    {
        $input = [
            'key1' => 'value1',
            'key2' => ['key21' => 'value21', 'key22' => 'value22', 'key23' => ['key231' => 'value231']],
            'key3' => ['key31' => 'value31', 'key3' => 'value3'],
            'key4' => ['key4' => 'value4'],
        ];
        $expectedOutput = [
            'key1' => 'value1',
            'key21' => 'value21',
            'key22' => 'value22',
            'key231' => 'value231',
            'key31' => 'value31',
            'key3' => 'value3',
            'key4' => 'value4',
        ];
        $output = ConvertArray::toFlatArray($input);
        $this->assertEquals($expectedOutput, $output, 'Array is converted to flat structure incorrectly.');
    }

    /**
     * @return array
     */
    public function assocToXmlExceptionDataProvider()
    {
        return [[[], ''], [[], 0], [[1, 2, 3]], [['root' => 1], 'root']];
    }
}
