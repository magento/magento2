<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\ConvertArray;
use PHPUnit\Framework\TestCase;

class ConvertArrayTest extends TestCase
{
    /**
     * @var ConvertArray
     */
    protected $_model;

    protected function setUp(): void
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

    public function testAssocToXmlExceptionByKey()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Associative and numeric keys can\'t be mixed at one level. Verify and try again.'
        );
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
     * @dataProvider assocToXmlExceptionDataProvider
     */
    public function testAssocToXmlException($array, $rootName = '_')
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
    public static function assocToXmlExceptionDataProvider()
    {
        return [[[], ''], [[], 0], [[1, 2, 3]], [['root' => 1], 'root']];
    }
}
