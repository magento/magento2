<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Config;

/**
 * @codingStandardsIgnoreFile
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\SchemaLocator
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\MessageQueue\Config\SchemaLocator();
    }

    public function testGetSchema()
    {
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/MessageQueue/etc/queue_merged.xsd');
        $actual = str_replace('\\', '/', $this->model->getSchema());
        $this->assertEquals($expected, $actual);
    }

    public function testGetPerFileSchema()
    {
        $expected = str_replace('\\', '/', BP . '/lib/internal/Magento/Framework/MessageQueue/etc/queue.xsd');
        $actual = str_replace('\\', '/', $this->model->getPerFileSchema());
        $this->assertEquals($expected, $actual);
    }
}
