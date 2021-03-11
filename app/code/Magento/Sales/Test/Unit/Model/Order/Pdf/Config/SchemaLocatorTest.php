<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator
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

    protected function setUp(): void
    {
        $this->_moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Sales'
        )->willReturn(
            $this->_xsdDir
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
