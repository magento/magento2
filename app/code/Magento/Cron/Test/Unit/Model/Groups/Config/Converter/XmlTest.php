<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Groups\Config\Converter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Groups\Config\Converter\Xml
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Magento\Cron\Model\Groups\Config\Converter\Xml();
    }

    public function testConvert()
    {
        $xmlExample = <<<XML
<config>
    <group id="test">
        <schedule_generate_every>1</schedule_generate_every>
    </group>
</config>
XML;

        $xml = new \DOMDocument();
        $xml->loadXML($xmlExample);

        $results = $this->object->convert($xml);
        $this->assertArrayHasKey('test', $results);
        $this->assertArrayHasKey('schedule_generate_every', $results['test']);
        $this->assertEquals('1', $results['test']['schedule_generate_every']['value']);
    }
}
