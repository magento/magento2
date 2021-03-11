<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Address\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
    {
        $this->_xsdFile = $this->_xsdDir . '/address_formats.xsd';
        $this->_moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Customer'
        )->willReturn(
            $this->_xsdDir
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
