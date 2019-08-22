<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular\Magento\Customer;

class AddressFormatsFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        /** @var \Magento\Customer\Model\Address\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Address\Config\SchemaLocator::class
        );
        $this->_schemaFile = $schemaLocator->getSchema();
    }

    /**
     * @param string $file
     * @dataProvider fileFormatDataProvider
     */
    public function testFileFormat($file)
    {
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file), $validationStateMock);
        $result = $dom->validate($this->_schemaFile, $errors);
        $this->assertTrue($result, print_r($errors, true));
    }

    /**
     * @return array
     */
    public function fileFormatDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles(
            '{*/address_formats.xml,address_formats.xml}'
        );
    }
}
