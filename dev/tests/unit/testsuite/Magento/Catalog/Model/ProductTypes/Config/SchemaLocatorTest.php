<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductTypes\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    protected function setUp()
    {
        $this->_moduleReaderMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->_moduleReaderMock->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Catalog'
        )->will(
            $this->returnValue('schema_dir')
        );
        $this->_model = new \Magento\Catalog\Model\ProductTypes\Config\SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/product_types_merged.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/product_types.xsd', $this->_model->getPerFileSchema());
    }
}
