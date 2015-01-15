<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Context;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var []
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $parentName;

    /**
     * @var Configuration
     */
    protected $configurationModel;

    public function setUp()
    {
        $this->configuration = ['key' => 'value'];
        $this->name = 'myName';
        $this->parentName = 'thisParentName';
        $this->configurationModel = new Configuration($this->name, $this->parentName, $this->configuration);
    }

    public function testGetData()
    {
        $this->assertEquals($this->configuration, $this->configurationModel->getData(null));
        $this->assertEquals(null, $this->configurationModel->getData('someKey'));
        $this->assertEquals('value', $this->configurationModel->getData('key'));
    }

    public function testAddData()
    {
        $this->configurationModel->addData('new_key', 'value1');
        $this->assertEquals('value1', $this->configurationModel->getData('new_key'));
    }

    public function testUpdateData()
    {
        $this->configurationModel->addData('new_key', 'value1');
        $this->assertEquals('value1', $this->configurationModel->getData('new_key'));
        $this->configurationModel->updateData('new_key', 'value2');
        $this->assertEquals('value2', $this->configurationModel->getData('new_key'));
    }

    public function testGetName()
    {
        $this->assertEquals($this->name, $this->configurationModel->getName());
    }

    public function testGetParentName()
    {
        $this->assertEquals($this->parentName, $this->configurationModel->getParentName());
    }
}
