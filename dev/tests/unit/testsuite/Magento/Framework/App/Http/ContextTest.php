<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Http;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $object;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManager->getObject('Magento\Framework\App\Http\Context');
    }

    public function testGetValue()
    {
        $this->assertNull($this->object->getValue('value'));
    }

    public function testSetGetValue()
    {
        $this->object->setValue('value', 'value', 'default');
        $this->assertEquals('value', $this->object->getValue('value'));
    }

    public function testSetUnsetGetValue()
    {
        $this->object->setValue('value', 'value', 'default');
        $this->object->unsValue('value');
        $this->assertEquals('default', $this->object->getValue('value'));
    }

    public function testGetData()
    {
        $this->object->setValue('value1', 'value1', 'default1');
        $this->object->setValue('value2', 'value2', 'default2');
        $this->object->setValue('value3', 'value3', 'value3');
        $this->object->unsValue('value1');
        $this->assertEquals(['value2' => 'value2'], $this->object->getData());
    }
}
