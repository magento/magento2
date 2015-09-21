<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Config;

/**
 * @codingStandardsIgnoreFile
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Amqp\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\Amqp\Config\SchemaLocator();
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    public function testGetSchema()
    {
        $expected = $this->urnResolver->getRealPath('urn:magento:framework:Amqp/etc/queue_merged.xsd');
        $actual = $this->model->getSchema();
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $expected = $this->urnResolver->getRealPath('urn:magento:framework:Amqp/etc/queue.xsd');
        $actual = $this->model->getPerFileSchema();
        $this->assertEquals($expected, $actual);
    }
}
