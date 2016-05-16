<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Http;

use \Magento\Framework\App\Http\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $object;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $this->objectManager->getObject('Magento\Framework\App\Http\Context');
    }

    public function testGetValue()
    {
        $this->assertNull($this->object->getValue('key'));
    }

    public function testSetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->assertEquals('value', $this->object->getValue('key'));
    }

    public function testSetUnsetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->object->unsValue('key');
        $this->assertEquals('default', $this->object->getValue('key'));
    }

    public function testGetData()
    {
        $this->object->setValue('key1', 'value1', 'default1');
        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key3', 'value3', 'value3');
        $this->object->unsValue('key1');
        $this->assertEquals(['key2' => 'value2'], $this->object->getData());
    }

    public function testGetVaryString()
    {
        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key1', 'value1', 'default1');
        $data = [
            'key2' => 'value2',
            'key1' => 'value1'
        ];
        ksort($data);
        $this->assertEquals(sha1(serialize($data)), $this->object->getVaryString());
    }
}
