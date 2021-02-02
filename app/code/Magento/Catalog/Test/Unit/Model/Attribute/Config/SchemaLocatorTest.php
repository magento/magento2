<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleReader;

    protected function setUp(): void
    {
        $this->_moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Catalog'
        )->willReturn(
            'fixture_dir'
        );
        $this->_model = new \Magento\Catalog\Model\Attribute\Config\SchemaLocator($this->_moduleReader);
    }

    public function testGetSchema()
    {
        $actualResult = $this->_model->getSchema();
        $this->assertEquals('fixture_dir/catalog_attributes.xsd', $actualResult);
        // Makes sure the value is calculated only once
        $this->assertEquals($actualResult, $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $actualResult = $this->_model->getPerFileSchema();
        $this->assertEquals('fixture_dir/catalog_attributes.xsd', $actualResult);
        // Makes sure the value is calculated only once
        $this->assertEquals($actualResult, $this->_model->getPerFileSchema());
    }
}
