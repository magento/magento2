<?php
/**
 * Test XML Renderer for REST.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Response\Renderer;

class XmlTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Webapi\Rest\Response\Renderer\Xml */
    protected $_restXmlRenderer;

    protected function setUp()
    {
        /** Prepare object for SUT constructor. */
        $xmlGenerator = new \Magento\Framework\Xml\Generator();
        /** Initialize SUT. */
        $this->_restXmlRenderer = new \Magento\Framework\Webapi\Rest\Response\Renderer\Xml($xmlGenerator);
        parent::setUp();
    }

    protected function tearDown()
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
                new \Magento\Framework\DataObject(['key' => 'value']),
                '<?xml version="1.0"?><response><key>value</key></response>',
                'Invalid XML render with \Magento\Framework\DataObject data.'
            ]
        ];
    }
}
