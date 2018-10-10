<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject\Test\Unit\Copy\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Copy\Config\SchemaLocator
     */
    protected $model;

    protected function setUp()
    {
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->exactly(2))
            ->method('getRealPath')
            ->will($this->returnCallback(function ($urn) {
                $urnParts = explode(':', $urn);
                return 'schema_dir/' . $urnParts[3];
            }));

        $this->model = new \Magento\Framework\DataObject\Copy\Config\SchemaLocator(
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
