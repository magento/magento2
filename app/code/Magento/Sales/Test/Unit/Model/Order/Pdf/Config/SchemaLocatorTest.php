<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

use Magento\Framework\Module\Dir\Reader;
use Magento\Sales\Model\Order\Pdf\Config\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $_model;

    /**
     * @var Reader|MockObject
     */
    protected $_moduleReader;

    /**
     * @var string
     */
    protected $_xsdDir = 'schema_dir';

    protected function setUp(): void
    {
        $this->_moduleReader = $this->createPartialMock(Reader::class, ['getModuleDir']);
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

        $this->_model = new SchemaLocator($this->_moduleReader);
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
