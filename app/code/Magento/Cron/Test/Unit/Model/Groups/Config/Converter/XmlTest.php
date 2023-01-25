<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\Groups\Config\Converter;

use Magento\Cron\Model\Groups\Config\Converter\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new Xml();
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
