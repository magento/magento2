<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var \Magento\ImportExport\Model\Import\Config\SchemaLocator
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);

        $this->_moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_ImportExport'
        )->willReturn(
            'schema_dir'
        );
        $this->_model = new \Magento\ImportExport\Model\Import\Config\SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/import_merged.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/import.xsd', $this->_model->getPerFileSchema());
    }
}
