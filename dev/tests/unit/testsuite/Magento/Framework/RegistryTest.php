<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            'value' => '\Magento\Customer\Model\Customer'
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
