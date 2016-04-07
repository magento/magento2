<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Simplexml\Test\Unit;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider xmlDataProvider
     */
    public function testUnsetSelf($xmlData)
    {
        /** @var $xml \Magento\Framework\Simplexml\Element */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertTrue(isset($xml->node3->node4));
        $xml->node3->unsetSelf();
        $this->assertFalse(isset($xml->node3->node4));
        $this->assertFalse(isset($xml->node3));
        $this->assertTrue(isset($xml->node1));
    }

    /**
     * @dataProvider xmlDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Root node could not be unset.
     */
    public function testGetParent($xmlData)
    {
        /** @var $xml \Magento\Framework\Simplexml\Element */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertTrue($xml->getName() == 'root');
        $xml->unsetSelf();
    }

    /**
     * Data Provider for testUnsetSelf and testUnsetSelfException
     */
    public static function xmlDataProvider()
    {
        return [
            [[__DIR__ . '/_files/data.xml', 'Magento\Framework\Simplexml\Element']]
        ];
    }

    public function testAsNiceXmlMixedData()
    {
        $dataFile = file_get_contents(__DIR__ . '/_files/mixed_data.xml');
        /** @var \Magento\Framework\Simplexml\Element $xml  */
        $xml = simplexml_load_string($dataFile, 'Magento\Framework\Simplexml\Element');

        $expected = <<<XML
<root>
   <node_1 id="1">Value 1
      <node_1_1>Value 1.1
         <node_1_1_1>Value 1.1.1</node_1_1_1>
      </node_1_1>
   </node_1>
   <node_2>
      <node_2_1>Value 2.1</node_2_1>
   </node_2>
</root>

XML;
        $this->assertEquals($expected, $xml->asNiceXml());
    }

    public function testAppendChild()
    {
        /** @var \Magento\Framework\Simplexml\Element $baseXml */
        $baseXml = simplexml_load_string('<root/>', 'Magento\Framework\Simplexml\Element');
        /** @var \Magento\Framework\Simplexml\Element $appendXml */
        $appendXml = simplexml_load_string(
            '<node_a attr="abc"><node_b innerAttribute="xyz">text</node_b></node_a>',
            'Magento\Framework\Simplexml\Element'
        );
        $baseXml->appendChild($appendXml);

        $expectedXml = '<root><node_a attr="abc"><node_b innerAttribute="xyz">text</node_b></node_a></root>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $baseXml->asNiceXml());
    }

    public function testSetNode()
    {
        $path = '/node1/node2';
        $value = 'value';
        /** @var \Magento\Framework\Simplexml\Element $xml */
        $xml = simplexml_load_string('<root/>', 'Magento\Framework\Simplexml\Element');
        $this->assertEmpty($xml->xpath('/root/node1/node2'));
        $xml->setNode($path, $value);
        $this->assertNotEmpty($xml->xpath('/root/node1/node2'));
        $this->assertEquals($value, (string)$xml->xpath('/root/node1/node2')[0]);
    }

    /**
     * @dataProvider setAttributeDataProvider
     * @param string $name
     * @param string $value
     */
    public function testSetAttribute($name, $value)
    {
        /** @var \Magento\Framework\Simplexml\Element $xml */
        $xml = simplexml_load_string('<root name="test2" data=""/>', 'Magento\Framework\Simplexml\Element');
        $this->assertEquals($xml->getAttribute('name'), 'test2');
        $this->assertNull($xml->getAttribute('new'));
        $xml->setAttribute($name, $value);
        $this->assertEquals($xml->getAttribute($name), $value);
    }

    public function setAttributeDataProvider()
    {
        return [
            ['name', 'test'],
            ['new', 'beard'],
            ['data', 'some-data']
        ];
    }
}
