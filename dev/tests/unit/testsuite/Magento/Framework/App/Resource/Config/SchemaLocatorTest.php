<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_expected;

    /**
     * @var \Magento\Framework\App\Resource\Config\SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        $this->_expected = str_replace('\\', '/', BP) . '/lib/internal/Magento/Framework/App/etc/resources.xsd';
        $this->_model = new \Magento\Framework\App\Resource\Config\SchemaLocator();
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->_expected, str_replace('\\', '/', $this->_model->getSchema()));
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals($this->_expected, str_replace('\\', '/', $this->_model->getPerFileSchema()));
    }
}
