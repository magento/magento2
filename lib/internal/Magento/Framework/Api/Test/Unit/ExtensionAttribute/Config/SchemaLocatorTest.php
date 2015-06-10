<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\ExtensionAttribute\Config;

/**
 * Test for \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator();
    }

    public function testGetSchema()
    {
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/Api/etc/extension_attributes.xsd');
        $actual = str_replace('\\', '/', $this->_model->getSchema());
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $actual = str_replace('\\', '/', $this->_model->getPerFileSchema());
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/Api/etc/extension_attributes.xsd');
        $this->assertEquals($expected, $actual);
    }
}
