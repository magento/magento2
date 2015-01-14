<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Resource\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\Resource\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    /**
     * @var \Magento\TestFramework\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        $this->_schemaLocator = new \Magento\Framework\Acl\Resource\Config\SchemaLocator();
        $this->_xsdSchema = $this->_schemaLocator->getSchema();
        $this->_xsdValidator = new \Magento\TestFramework\Utility\XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_acl.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to acl.xsd
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidAclXmlArray.php';
    }
}
