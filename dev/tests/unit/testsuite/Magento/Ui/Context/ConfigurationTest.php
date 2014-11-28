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
 