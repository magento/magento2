<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\SearchEngine\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Search\SearchEngine\Config\SchemaLocator;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    private $model;

    protected function setUp(): void
    {
        $urnResolver = $this->createMock(UrnResolver::class);
        $urnResolver->expects($this->any())
            ->method('getRealPath')
            ->with(SchemaLocator::SEARCH_ENGINE_XSD_PATH)
            ->willReturn('xsd/path');

        $this->model = new SchemaLocator($urnResolver);
    }

    public function testGetSchema()
    {
        $this->assertEquals('xsd/path', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('xsd/path', $this->model->getPerFileSchema());
    }
}
