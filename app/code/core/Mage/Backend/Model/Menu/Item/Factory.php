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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Menu_Item_Factory
{
    /**
     * ACL
     *
     * @var Mage_Core_Model_Authorization
     */
    protected $_acl;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * @var Mage_Backend_Model_Menu_Factory
     */
    protected $_menuFactory;

    /**
     * @var Mage_Core_Helper_Abstract[]
     */
    protected $_helpers = array();

    /**
     * @var Mage_Backend_Model_Url
     */
    protected $_urlModel;

    /**
     * Application Configuration
     *
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * Store Configuration
     *
     * @var Mage_Core_Model_Store_Config
     */
    protected $_storeConfig;

    /**
     * Menu item parameter validator
     *
     * @var Mage_Backend_Model_Menu_Item_Validator
     */
    protected $_validator;

    /**
     * @param Magento_ObjectManager $factory
     * @param Mage_Core_Model_Authorization $authorization
     * @param Mage_Backend_Model_Menu_Factory $menuFactory
     * @param Mage_Core_Model_Config $applicationConfig
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Backend_Model_Url $urlModel
     * @param Mage_Backend_Model_Menu_Item_Validator $menuItemValidator
     * @param array $data
     */
    public function __construct(
        Magento_ObjectManager $factory,
        Mage_Core_Model_Authorization $authorization,
        Mage_Backend_Model_Menu_Factory $menuFactory,
        Mage_Core_Model_Config $applicationConfig,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Backend_Model_Url $urlModel,
        Mage_Backend_Model_Menu_Item_Validator $menuItemValidator,
        array $data = array()
    ) {
        $this->_acl = $authorization;
        $this->_objectFactory = $factory;
        $this->_menuFactory = $menuFactory;
        $this->_appConfig = $applicationConfig;
        $this->_storeConfig = $storeConfig;
        $this->_urlModel = $urlModel;
        $this->_validator = $menuItemValidator;

        if (isset($data['helpers'])) {
            $this->_helpers = $data['helpers'];
        }
    }

    /**
     * Create menu item from array
     *
     * @param array $data
     * @return Mage_Backend_Model_Menu_Item
     */
    public function createFromArray(array $data = array())
    {
        $module = 'Mage_Backend_Helper_Data';
        if (isset($data['module'])) {
            $module = $data['module'];
            unset($data['module']);
        }

        $data = array('data' => $data);

        $data['authorization'] = $this->_acl;
        $data['applicationConfig'] = $this->_appConfig;
        $data['storeConfig'] = $this->_storeConfig;
        $data['menuFactory'] = $this->_menuFactory;
        $data['urlModel'] = $this->_urlModel;
        $data['validator'] = $this->_validator;
        $data['helper'] = isset($this->_helpers[$module]) ? $this->_helpers[$module] : Mage::helper($module);
        return $this->_objectFactory->create('Mage_Backend_Model_Menu_Item', $data);
    }
}
