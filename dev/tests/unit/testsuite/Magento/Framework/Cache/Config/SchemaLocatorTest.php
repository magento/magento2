<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Cache\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Config\SchemaLocator
     */
    protected $schemaLocator;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->schemaLocator = $objectManager->getObject('Magento\Framework\Cache\Config\SchemaLocator');
    }

    public function testGetSchema()
    {
        $this->assertRegExp('/etc[\/\\\\]cache.xsd/', $this->schemaLocator->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(null, $this->schemaLocator->getPerFileSchema());
    }
}
