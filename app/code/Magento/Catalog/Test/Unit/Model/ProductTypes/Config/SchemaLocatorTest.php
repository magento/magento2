<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleReaderMock;

    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->_moduleReaderMock->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Catalog'
        )->willReturn(
            'schema_dir'
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
