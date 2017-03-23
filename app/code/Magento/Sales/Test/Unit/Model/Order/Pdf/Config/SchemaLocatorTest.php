<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReader;

    /**
     * @var string
     */
    protected $_xsdDir = 'schema_dir';

    protected function setUp()
    {
        $this->_moduleReader = $this->getMock(
            \Magento\Framework\Module\Dir\Reader::class,
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
            'Magento_Sales'
        )->will(
            $this->returnValue($this->_xsdDir)
        );

        $this->_model = new \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator($this->_moduleReader);
    }

    public function testGetSchema()
    {
        $file = $this->_xsdDir . '/pdf.xsd';
        $this->assertEquals($file, $this->_model->getSchema());
        // Make sure the value is calculated only once
        $this->assertEquals($file, $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $file = $this->_xsdDir . '/pdf_file.xsd';
        $this->assertEquals($file, $this->_model->getPerFileSchema());
        // Make sure the value is calculated only once
        $this->assertEquals($file, $this->_model->getPerFileSchema());
    }
}
