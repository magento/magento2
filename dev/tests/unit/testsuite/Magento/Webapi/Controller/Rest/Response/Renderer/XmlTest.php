<?php
/**
 * Test XML Renderer for REST.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Response\Renderer\Xml */
    protected $_restXmlRenderer;

    protected function setUp()
    {
        /** Prepare object for SUT constructor. */
        $xmlGenerator = new \Magento\Framework\Xml\Generator();
        /** Initialize SUT. */
        $this->_restXmlRenderer = new \Magento\Webapi\Controller\Rest\Response\Renderer\Xml($xmlGenerator);
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
        return array(
            // Each array consists of data to render, expected XML and assert message
            array(
                array('value1', 'value2'),
                '<?xml version="1.0"?><response><item>value1</item><item>value2</item></response>',
                'Invalid XML render of unassociated array data.'
            ),
            array(
                array('key1' => 'value1', 'key2' => 'value2'),
                '<?xml version="1.0"?><response><key1>value1</key1><key2>value2</key2></response>',
                'Invalid XML render of associated array data.'
            ),
            array(
                (object)array('key' => 'value'),
                '<?xml version="1.0"?><response><key>value</key></response>',
                'Invalid XML render of object data.'
            ),
            array(
                array('7key' => 'value'),
                '<?xml version="1.0"?><response><item_7key>value</item_7key></response>',
                'Invalid XML render with numeric symbol in data index.'
            ),
            array(
                array('.key' => 'value'),
                '<?xml version="1.0"?><response><item_key>value</item_key></response>',
                'Invalid XML render with "." symbol in data index.'
            ),
            array(
                array('-key' => 'value'),
                '<?xml version="1.0"?><response><item_-key>value</item_-key></response>',
                'Invalid XML render with "-" symbol in data index.'
            ),
            array(
                array(' prefix key:' => 'value'),
                '<?xml version="1.0"?><response><prefix_key>value</prefix_key></response>',
                'Invalid XML render with data key trimming.'
            ),
            array(
                'data',
                '<?xml version="1.0"?><response>data</response>',
                'Invalid XML render with simple data.'
            ),
            array(
                new \Magento\Framework\Object(array('key' => 'value')),
                '<?xml version="1.0"?><response><key>value</key></response>',
                'Invalid XML render with \Magento\Framework\Object data.'
            )
        );
    }
}
