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
 * @category    Tools
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/Acl/Db/Adapter/Factory.php';

class Tools_Migration_Acl_Db_Adapter_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_Acl_Db_Adapter_Factory
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_config;

    public function setUp()
    {
        $this->_model = new Tools_Migration_Acl_Db_Adapter_Factory();
        $this->_config = array(
            'dbname' => 'some_db_name',
            'password' => '',
            'username' => '',

        );
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @return array
     */
    public function getAdapterDataProvider()
    {
        return array(
            array('Varien_Db_Adapter_Pdo_Mysql'),
            array(''),
            array(null),
        );
    }

    /**
     * @param $adapterType
     * @dataProvider getAdapterDataProvider
     */
    public function testGetAdapter($adapterType)
    {
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract',
            $this->_model->getAdapter($this->_config, $adapterType)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetAdapterWithInvalidType()
    {
        $this->_model->getAdapter($this->_config, 'Mage_Core_Helper_Data');
    }
}
