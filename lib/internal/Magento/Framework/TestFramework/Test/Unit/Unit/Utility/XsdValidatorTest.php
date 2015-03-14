<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Test\Unit\Unit\Utility;

class XsdValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
     */
    protected $_validator;

    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    protected function setUp()
    {
        $this->_validator = new \Magento\Framework\TestFramework\Unit\Utility\XsdValidator();
        $this->_xsdSchema = realpath(__DIR__ . '/_files/valid.xsd');
    }

    public function testValidXml()
    {
        $xmlFile = realpath(__DIR__ . '/_files/valid.xml');
        $xmlString = file_get_contents($xmlFile);

        $this->assertEquals([], $this->_validator->validate($this->_xsdSchema, $xmlString));
    }

    public function testInvalidXml()
    {
        $xmlFile = realpath(__DIR__ . '/_files/invalid.xml');
        $expected = [
            "Element 'block', attribute 'type': The attribute 'type' is not allowed.",
            "Element 'actions': This element is not expected. Expected is ( property ).",
        ];
        $xmlString = file_get_contents($xmlFile);

        $this->assertEquals($expected, $this->_validator->validate($this->_xsdSchema, $xmlString));
    }
}
