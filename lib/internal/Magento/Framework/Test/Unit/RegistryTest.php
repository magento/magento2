<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use PHPUnit\Framework\TestCase;

/**
 * Registry model test. Test cases for managing values in registry
 */
class RegistryTest extends TestCase
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $data;

    protected function setUp(): void
    {
        $this->registry = new Registry();
        $this->data = [
            'key' => 'customer',
            'value' => Customer::class,
        ];
        $this->registry->register($this->data['key'], $this->data['value']);
    }

    protected function tearDown(): void
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

    public function testRegisterKeyExists()
    {
        $this->expectException('RuntimeException');
        $this->registry->register($this->data['key'], $this->data['value']);
    }

    public function testUnregister()
    {
        $key = 'csv_adapter';
        $valueObj = $this->createMock(Csv::class);
        $this->registry->register($key, $valueObj);
        $this->assertEquals($valueObj, $this->registry->registry($key));
        $this->registry->unregister($key);
        $this->assertNull($this->registry->registry($key));
        $this->registry->unregister($this->data['key']);
        $this->assertNull($this->registry->registry($this->data['key']));
    }
}
