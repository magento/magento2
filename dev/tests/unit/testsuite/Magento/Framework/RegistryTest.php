<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Registry model test. Test cases for managing values in registry
 */
class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $data;

    public function setUp()
    {
        $this->registry = new Registry();
        $this->data = [
            'key' => 'customer',
            'value' => '\Magento\Customer\Model\Customer',
        ];
        $this->registry->register($this->data['key'], $this->data['value']);
    }

    public function tearDown()
    {
        unset($this->registry);
    }

    public function testRegistry()
    {
        $this->assertEquals($this->data['value'], $this->registry->registry($this->data['key']));
        $this->assertNull($this->registry->registry($this->data['value']));
    }

    public function testRegister()
    {
        $key = 'email';
        $value = 'test@magento.com';
        $this->registry->register($key, $value);
        $this->assertEquals($value, $this->registry->registry($key));
        $key = 'name';
        $graceful = true;
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterKeyExists()
    {
        $this->registry->register($this->data['key'], $this->data['value']);
    }

    public function testUnregister()
    {
        $key = 'csv_adapter';
        $valueObj = $this->getMock('\Magento\ImportExport\Model\Export\Adapter\Csv', [], [], '', false, false);
        $this->registry->register($key, $valueObj);
        $this->assertEquals($valueObj, $this->registry->registry($key));
        $this->registry->unregister($key);
        $this->assertNull($this->registry->registry($key));
        $this->registry->unregister($this->data['key']);
        $this->assertNull($this->registry->registry($this->data['key']));
    }
}
