<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Simplexml\Test\Unit;

use Magento\Framework\Simplexml\Config;
use Magento\Framework\Simplexml\Element;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $config;

    protected function setUp(): void
    {
        $this->config = new Config();
    }

    public function testConstruct()
    {
        $xml = '<root><node1><node2/></node1><node3><node4/></node3></root>';
        $file = __DIR__ . '/_files/data.xml';

        $config = new Config($xml);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());

        $config = new Config($file);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());

        /** @var Element $simpleXml */
        $simpleXml = simplexml_load_string(file_get_contents($file), Element::class);
        $config = new Config($simpleXml);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());
    }

    public function testLoadString()
    {
        $xml = '<?xml version="1.0"?><config><node>1</node></config>';
        $this->assertFalse($this->config->loadString(''));
        $this->assertTrue($this->config->loadString($xml));
        $this->assertXmlStringEqualsXmlString($xml, $this->config->getXmlString());
    }

    public function testLoadDom()
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><config><node>1</node></config>');
        $this->assertTrue($this->config->loadDom($dom));
    }

    public function testGetNode()
    {
        $this->assertFalse($this->config->getNode());
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $this->assertSame('Value 2.1', $config->getNode('node_2/node_2_1')->asArray());
    }

    public function testGetXpath()
    {
        $this->assertFalse($this->config->getXpath('wrong_xpath'));
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $this->assertFalse($config->getXpath('wrong_xpath'));
        $element = $config->getXpath('/root/node_2/node_2_1');
        $this->assertArrayHasKey(0, $element);
        $this->assertInstanceOf(Element::class, $element[0]);
        $this->assertSame('Value 2.1', $element[0]->asArray());
    }

    public function testLoadWrongFile()
    {
        $this->assertFalse($this->config->loadFile('wrong_file'));
    }

    public function testSetNode()
    {
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $config->setNode('node_2', 'new_value');
        $this->assertSame('new_value', $config->getNode('node_2')->asArray());
    }

    public function testApplyExtends()
    {
        $config = new Config(__DIR__ . '/_files/extend_data.xml');
        $config->applyExtends();
        $this->assertEquals(
            $config->getNode('node_1/node_1_1')->asArray(),
            $config->getNode('node_3/node_1_1')->asArray()
        );
        $config = new Config(__DIR__ . '/_files/data.xml');
        $config->applyExtends();
    }

    public function testExtendNode()
    {
        $config = new Config(__DIR__ . '/_files/data.xml');
        $config->extend(new Config('<config><node>1</node></config>'));
        $this->assertSame('1', $config->getNode('node')->asArray());
    }
}
