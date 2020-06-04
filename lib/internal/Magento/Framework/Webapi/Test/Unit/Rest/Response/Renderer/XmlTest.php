<?php
/**
 * Test XML Renderer for REST.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest\Response\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Response\Renderer\Xml;
use Magento\Framework\Xml\Generator;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /** @var Xml */
    protected $_restXmlRenderer;

    protected function setUp(): void
    {
        /** Prepare object for SUT constructor. */
        $xmlGenerator = new Generator();
        /** Initialize SUT. */
        $this->_restXmlRenderer = new Xml($xmlGenerator);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->_restXmlRenderer);
        parent::tearDown();
    }

    /**
     * Test render method.
     *
     * @dataProvider providerXmlRender
     */
    public function testRender($dataToRender, $expectedXml, $message)
    {
        $actualXml = $this->_restXmlRenderer->render($dataToRender);
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message);
    }

    /**
     * Test GetMimeType method.
     */
    public function testGetMimeType()
    {
        $exceptedMimeType = 'application/xml';
        $this->assertEquals($exceptedMimeType, $this->_restXmlRenderer->getMimeType(), 'Unexpected mime type.');
    }

    /**
     * Data provider for render method testing.
     *
     * @return array
     */
    public function providerXmlRender()
    {
        return [
            // Each array consists of data to render, expected XML and assert message
            [
                ['value1', 'value2'],
                '<?xml version="1.0"?><response><item>value1</item><item>value2</item></response>',
                'Invalid XML render of unassociated array data.'
            ],
            [
                ['key1' => 'value1', 'key2' => 'value2'],
                '<?xml version="1.0"?><response><key1>value1</key1><key2>value2</key2></response>',
                'Invalid XML render of associated array data.'
            ],
            [
                (object)['key' => 'value'],
                '<?xml version="1.0"?><response><key>value</key></response>',
                'Invalid XML render of object data.'
            ],
            [
                ['7key' => 'value'],
                '<?xml version="1.0"?><response><item_7key>value</item_7key></response>',
                'Invalid XML render with numeric symbol in data index.'
            ],
            [
                ['key' => 'test & foo'],
                '<?xml version="1.0"?><response><key>test &amp; foo</key></response>',
                'Invalid XML render with ampersand symbol in data index.'
            ],
            [
                ['.key' => 'value'],
                '<?xml version="1.0"?><response><item_key>value</item_key></response>',
                'Invalid XML render with "." symbol in data index.'
            ],
            [
                ['-key' => 'value'],
                '<?xml version="1.0"?><response><item_-key>value</item_-key></response>',
                'Invalid XML render with "-" symbol in data index.'
            ],
            [
                [' prefix key:' => 'value'],
                '<?xml version="1.0"?><response><prefix_key>value</prefix_key></response>',
                'Invalid XML render with data key trimming.'
            ],
            [
                'data',
                '<?xml version="1.0"?><response>data</response>',
                'Invalid XML render with simple data.'
            ],
            [
                new DataObject(['key' => 'value']),
                '<?xml version="1.0"?><response><key>value</key></response>',
                'Invalid XML render with \Magento\Framework\DataObject data.'
            ]
        ];
    }
}
