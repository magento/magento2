<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Unit\Utility;

use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\TestCase;

class XsdValidatorTest extends TestCase
{
    /**
     * @var XsdValidator
     */
    protected $_validator;

    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_validator = new XsdValidator();
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
            "Element 'block', attribute 'type': The attribute 'type' is not allowed.\nLine: 9\n",
            "Element 'actions': This element is not expected. Expected is ( property ).\nLine: 10\n",
        ];
        $xmlString = file_get_contents($xmlFile);

        $this->assertEquals($expected, $this->_validator->validate($this->_xsdSchema, $xmlString));
    }
}
