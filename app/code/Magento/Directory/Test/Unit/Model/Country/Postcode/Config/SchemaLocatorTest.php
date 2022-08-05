<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

use Magento\Directory\Model\Country\Postcode\Config\SchemaLocator;
use Magento\Framework\Module\Dir\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->createMock(Reader::class);
        $this->moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Directory'
        )->willReturn(
            'schema_dir'
        );

        $this->model = new SchemaLocator($this->moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/zip_codes.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/zip_codes.xsd', $this->model->getPerFileSchema());
    }
}
