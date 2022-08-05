<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Simplexml\Test\Unit;

use Magento\Framework\Simplexml\Element;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    /**
     * @dataProvider xmlDataProvider
     */
    public function testUnsetSelf($xmlData)
    {
        /** @var Element $xml */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertObjectHasAttribute('node4', $xml->node3);
        $xml->node3->unsetSelf();
        $this->assertObjectNotHasAttribute('node4', $xml->node3);
        $this->assertObjectNotHasAttribute('node3', $xml);
        $this->assertObjectHasAttribute('node1', $xml);
    }

    /**
     * @dataProvider xmlDataProvider
     */
    public function testGetParent($xmlData)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Root node could not be unset.');
        /** @var Element $xml */
        $xml = simplexml_load_file($xmlData[0], $xmlData[1]);
        $this->assertEquals('root', $xml->getName());
        $xml->unsetSelf();
    }

    /**
     * Data Provider for testUnsetSelf and testUnsetSelfException
     */
    public static function xmlDataProvider()
    {
        return [
            [[__DIR__ . '/_files/data.xml', Element::class]]
        ];
    }

    public function testAsNiceXmlMixedData()
    {
        $dataFile = file_get_contents(__DIR__ . '/_files/mixed_data.xml');
        /** @var Element $xml  */
        $xml = simplexml_load_string($dataFile, Element::class);

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
        /** @var Element $baseXml */
        $baseXml = simplexml_load_string('<root/>', Element::class);
        /** @var Element $appendXml */
        $appendXml = simplexml_load_string(
            '<node_a attr="abc"><node_b innerAttribute="xyz">text</node_b></node_a>',
            Element::class
        );
        $baseXml->appendChild($appendXml);

        $expectedXml = '<root><node_a attr="abc"><node_b innerAttribute="xyz">text</node_b></node_a></root>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $baseXml->asNiceXml());
    }

    public function testSetNode()
    {
        $path = '/node1/node2';
        $value = 'value';
        /** @var Element $xml */
        $xml = simplexml_load_string('<root/>', Element::class);
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
        /** @var Element $xml */
        $xml = simplexml_load_string('<root name="test2" data=""/>', Element::class);
        $this->assertEquals($xml->getAttribute('name'), 'test2');
        $this->assertNull($xml->getAttribute('new'));
        $xml->setAttribute($name, $value);
        $this->assertEquals($xml->getAttribute($name), $value);
    }

    /**
     * @return array
     */
    public function setAttributeDataProvider()
    {
        return [
            ['name', 'test'],
            ['new', 'beard'],
            ['data', 'some-data']
        ];
    }
}
