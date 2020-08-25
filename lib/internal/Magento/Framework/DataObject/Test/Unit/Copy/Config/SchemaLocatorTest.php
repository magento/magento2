<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject\Test\Unit\Copy\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\DataObject\Copy\Config\SchemaLocator;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $model;

    protected function setUp(): void
    {
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->exactly(2))
            ->method('getRealPath')
            ->willReturnCallback(function ($urn) {
                $urnParts = explode(':', $urn);
                return 'schema_dir/' . $urnParts[3];
            });

        $this->model = new SchemaLocator(
            $urnResolverMock,
            'urn:magento:framework:DataObject/etc/schema.xsd',
            'urn:magento:framework:DataObject/etc/perFileSchema.xsd'
        );
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/DataObject/etc/schema.xsd', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/DataObject/etc/perFileSchema.xsd', $this->model->getPerFileSchema());
    }
}
