<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Asset\PropertyGroup;

class PropertyGroupTest extends TestCase
{
    /**
     * @var PropertyGroup
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = new PropertyGroup(['test_property' => 'test_value']);
    }

    public function testGetProperties()
    {
        $this->assertEquals(['test_property' => 'test_value'], $this->_object->getProperties());
    }

    public function testGetProperty()
    {
        $this->assertEquals('test_value', $this->_object->getProperty('test_property'));
        $this->assertNull($this->_object->getProperty('non_existing_property'));
    }
}
