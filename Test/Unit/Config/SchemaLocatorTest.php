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

    protected function setUp()
    {
        $this->model = new \Magento\Framework\Amqp\Config\SchemaLocator();
    }

    public function testGetSchema()
    {
        $expected = str_replace('\\', '/', realpath(__DIR__ . '/../../../etc/queue_merged.xsd'));
        $actual = str_replace('\\', '/', $this->model->getSchema());
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $expected = str_replace('\\', '/', realpath(__DIR__ . '/../../../etc/queue.xsd'));
        $actual = str_replace('\\', '/', $this->model->getPerFileSchema());
        $this->assertEquals($expected, $actual);
    }
}
