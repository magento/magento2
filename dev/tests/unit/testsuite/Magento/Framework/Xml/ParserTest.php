<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Xml;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Xml\Parser */
    protected $parser;

    protected function setUp()
    {
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
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage DOMDocument::schemaValidate(): Invalid Schema
     */
    public function testLoadAndValidateFail()
    {
	$this->parser->setExceptionName('\Magento\Framework\Exception');
	$this->parser->loadAndValidate(__DIR__ . '/_files/data1.xml', __DIR__ . '/_files/sample.xml');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage DOMDocument::schemaValidate(): Invalid Schema
     */
    public function testLoadXMLAndValidateFail()
    {
	$this->parser->setExceptionName('\InvalidArgumentException');
	$content = file_get_contents(__DIR__ . '/_files/data1.xml');
	$this->parser->loadXMLandValidate($content, __DIR__ . '/_files/sample.xml');
    }


    public function testValidateDomDocument()
    {
	$schemaFile = __DIR__ . '/_files/sample.xsd';
	$domMock = $this->getMock('DOMDocument', ['schemaValidate'], []);
	$domMock->expects($this->once())
	    ->method('schemaValidate')
	    ->with($schemaFile)
	    ->will($this->returnValue(true));
	$this->assertEquals(
	    [],
	    \Magento\Framework\Xml\Parser::validateDomDocument($domMock, $schemaFile)
	);
    }

    public function testUnknownValidationError()
    {
	$schemaFile = __DIR__ . '/_files/sample.xsd';
	$domMock = $this->getMock('DOMDocument', ['schemaValidate'], []);
	$domMock->expects($this->once())
	    ->method('schemaValidate')
	    ->with($schemaFile)
	    ->will($this->returnValue(false));
	$this->assertEquals(
	    ['Unknown validation error'],
	    \Magento\Framework\Xml\Parser::validateDomDocument($domMock, $schemaFile)
	);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error format '%th%is%is%invalid%' contains unsupported placeholders.
     */
    public function testInvalidArgumentException()
    {
	$schemaFile = __DIR__ . '/_files/sample.xsd';
	$this->parser->load(__DIR__ . '/_files/data2.xml');
	\Magento\Framework\Xml\Parser::validateDomDocument($this->parser->getDom(), $schemaFile, '%th%is%is%invalid%');
    }
}
