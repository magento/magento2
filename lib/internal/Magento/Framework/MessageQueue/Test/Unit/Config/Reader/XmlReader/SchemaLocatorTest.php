<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\XmlReader;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->model = new \Magento\Framework\MessageQueue\Config\Reader\Xml\SchemaLocator($this->urnResolver);
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
