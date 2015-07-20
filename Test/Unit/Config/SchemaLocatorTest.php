<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Config;


class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Config\SchemaLocator
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\Amqp\Config\SchemaLocator();
    }

    public function testGetSchema()
    {
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/Amqp/etc/queue.xsd');
        $actual = str_replace('\\', '/', $this->model->getSchema());
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $actual = str_replace('\\', '/', $this->model->getPerFileSchema());
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/Amqp/etc/events.xsd');
        $this->assertEquals($expected, $actual);
    }
}
