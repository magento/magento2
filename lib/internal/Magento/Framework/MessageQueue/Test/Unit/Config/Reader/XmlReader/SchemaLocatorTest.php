<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\XmlReader;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\MessageQueue\Config\Reader\Xml\SchemaLocator;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $model;

    /** @var UrnResolver */
    protected $urnResolver;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        $this->model = new SchemaLocator($this->urnResolver);
    }

    public function testGetSchema()
    {
        $expected = $this->urnResolver->getRealPath('urn:magento:framework-message-queue:etc/queue_merged.xsd');
        $actual = $this->model->getSchema();
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $expected = $this->urnResolver->getRealPath('urn:magento:framework-message-queue:etc/queue.xsd');
        $actual = $this->model->getPerFileSchema();
        $this->assertEquals($expected, $actual);
    }
}
