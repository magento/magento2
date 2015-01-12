<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Layout;

class XsdTest extends \PHPUnit_Framework_TestCase
{
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
        $this->_xsdSchema = BP . '/lib/internal/Magento/Framework/View/Layout/etc/page_configuration.xsd';
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

    /**
     * Get array of invalid xml strings
     *
     * @return array
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidLayoutArgumentsXmlArray.php';
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/arguments.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult, join("\n", $actualResult));
    }
}
