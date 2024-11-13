<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class LayoutConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Path to schema file
     *
     * @var string
     */
    protected $schemaFile;

    protected function setUp(): void
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/elements.xsd');
    }

    /**
     * Test a valid layout XML file
     */
    public function testValidLayoutXmlFile()
    {
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')->willReturn(true);
        $domConfig = new \Magento\Framework\Config\Dom(
            '<referenceBlock name="product.info.something" group="column_left"></referenceBlock>',
            $validationStateMock
        );
        $result = $domConfig->validate($this->schemaFile, $errors);
        $this->assertTrue($result);
        $this->assertEmpty($errors);
    }

    /**
     * Test a layout XML file having an invalid tag element
     */
    public function testBrokenLayoutXmlFile()
    {
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')->willReturn(true);
        $domConfig = new \Magento\Framework\Config\Dom(
            '<invalidElement name="some.name"></invalidElement>',
            $validationStateMock
        );
        $result = $domConfig->validate($this->schemaFile, $errors);
        $this->assertFalse($result);
        $this->assertNotEmpty($errors);
    }
}
