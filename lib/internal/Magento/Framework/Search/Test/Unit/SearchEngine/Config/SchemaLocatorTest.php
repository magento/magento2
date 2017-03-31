<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\SearchEngine\Config;

use Magento\Framework\Search\SearchEngine\Config\SchemaLocator;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SchemaLocator
     */
    private $model;

    protected function setUp()
    {
        $urnResolver = $this->getMock(\Magento\Framework\Config\Dom\UrnResolver::class, [], [], '', false);
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
