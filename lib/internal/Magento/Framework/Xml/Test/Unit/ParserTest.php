<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Xml\Test\Unit;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Xml\Parser */
    protected $parser;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->parser = new \Magento\Framework\Xml\Parser();
    }

    public function testGetXml()
    {
        $this->assertEquals(
            ['data' => [
                'nodes' => [
                    'text' => ' some text ',
                    'trim_spaces' => '',
                    'cdata' => '  Some data here <strong>html</strong> tags are <i>allowed</i>  ',
                    'zero' => '0',
                    'null' => null,
                ]
            ]],
            $this->parser->load(__DIR__ . '/_files/data.xml')->xmlToArray()
        );
    }

    /**
     */
    public function testLoadXmlInvalid()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('DOMDocument::loadXML(): Opening and ending tag mismatch');

        $sampleInvalidXml = '<?xml version="1.0"?><config></onfig>';
        $this->parser->initErrorHandler();
        $this->parser->loadXML($sampleInvalidXml);
    }
}
