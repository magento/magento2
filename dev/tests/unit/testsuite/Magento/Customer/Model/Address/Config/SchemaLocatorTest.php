<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReader;

    /**
     * @var string
     */
    protected $_xsdDir = 'schema_dir';

    /**
     * @var string
     */
    protected $_xsdFile;

    protected function setUp()
    {
        $this->_xsdFile = $this->_xsdDir . '/address_formats.xsd';
        $this->_moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            ['getModuleDir'],
            [],
            '',
            false
        );
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Customer'
        )->will(
            $this->returnValue($this->_xsdDir)
        );

        $this->_model = new \Magento\Customer\Model\Address\Config\SchemaLocator($this->_moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->_xsdFile, $this->_model->getSchema());
        // Makes sure the value is calculated only once
        $this->assertEquals($this->_xsdFile, $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals($this->_xsdFile, $this->_model->getPerFileSchema());
        // Makes sure the value is calculated only once
        $this->assertEquals($this->_xsdFile, $this->_model->getPerFileSchema());
    }
}
