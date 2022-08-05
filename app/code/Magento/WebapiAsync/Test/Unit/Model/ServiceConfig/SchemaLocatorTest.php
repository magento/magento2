<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\ServiceConfig;

use Magento\Framework\Module\Dir\Reader;
use Magento\WebapiAsync\Model\ServiceConfig\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    private $model;

    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->createPartialMock(
            Reader::class,
            ['getModuleDir']
        );
        $this->moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_WebapiAsync'
        )->willReturn(
            'schema_dir'
        );

        $this->model = new SchemaLocator($this->moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/webapi_async.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertNull($this->model->getPerFileSchema());
    }
}
