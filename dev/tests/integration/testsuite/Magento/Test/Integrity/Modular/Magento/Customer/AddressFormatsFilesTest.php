<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Integrity\Modular\Magento\Customer;

class AddressFormatsFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        /** @var \Magento\Customer\Model\Address\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Address\Config\SchemaLocator'
        );
        $this->_schemaFile = $schemaLocator->getSchema();
    }

    /**
     * @param string $file
     * @dataProvider fileFormatDataProvider
     */
    public function testFileFormat($file)
    {
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $result = $dom->validate($this->_schemaFile, $errors);
        $this->assertTrue($result, print_r($errors, true));
    }

    /**
     * @return array
     */
    public function fileFormatDataProvider()
    {
        return \Magento\Framework\Test\Utility\Files::init()->getConfigFiles(
            '{*/address_formats.xml,address_formats.xml}'
        );
    }
}
