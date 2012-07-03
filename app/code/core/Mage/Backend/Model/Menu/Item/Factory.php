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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Menu_Item_Factory
{
    /**
     * ACL
     *
     * @var Mage_Backend_Model_Auth_Session
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
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = array())
    {
        $this->_acl = isset($data['acl']) ? $data['acl'] : Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        if (!($this->_acl instanceof Mage_Backend_Model_Auth_Session)) {
            throw new InvalidArgumentException('Wrong acl object provided');
        }

        $this->_objectFactory = isset($data['objectFactory']) ? $data['objectFactory'] : Mage::getConfig();
        if (!($this->_objectFactory instanceof Mage_Core_Model_Config)) {
            throw new InvalidArgumentException('Wrong object factory provided');
        }

        $this->_menuFactory = isset($data['menuFactory'])
            ? $data['menuFactory']
            : Mage::getModel('Mage_Backend_Model_Menu_Factory');
        if (!($this->_menuFactory instanceof Mage_Backend_Model_Menu_Factory)) {
            throw new InvalidArgumentException('Wrong menu factory provided');
        }

        $this->_appConfig = isset($data['appConfig']) ? $data['appConfig']: Mage::getConfig();
        if (!($this->_appConfig instanceof Mage_Core_Model_Config)) {
            throw new InvalidArgumentException('Wrong application config provided');
        }

        $this->_storeConfig = isset($data['storeConfig'])
            ? $data['storeConfig']
            : Mage::getSingleton('Mage_Core_Model_Store_Config');
        if (!($this->_storeConfig instanceof Mage_Core_Model_Store_Config)) {
            throw new InvalidArgumentException('Wrong store config provided');
        }

        $this->_urlModel = isset($data['urlModel']) ? $data['urlModel'] : Mage::getSingleton('Mage_Backend_Model_Url');
        if (!($this->_urlModel instanceof Mage_Backend_Model_Url)) {
            throw new InvalidArgumentException('Wrong url model provided');
        }

        $this->_validator = isset($data['validator'])
            ? $data['validator']
            : Mage::getSingleton('Mage_Backend_Model_Menu_Item_Validator');
        if (!($this->_validator instanceof Mage_Backend_Model_Menu_Item_Validator)) {
            throw new InvalidArgumentException('Wrong item validator model provided');
        }

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
        }

        $data['module'] = isset($this->_helpers[$module]) ? $this->_helpers[$module] : Mage::helper($module);
        $data['acl'] = $this->_acl;
        $data['appConfig'] = $this->_appConfig;
        $data['storeConfig'] = $this->_storeConfig;
        $data['menuFactory'] = $this->_menuFactory;
        $data['urlModel'] = $this->_urlModel;
        $data['validator'] = $this->_validator;
        return $this->_objectFactory->getModelInstance('Mage_Backend_Model_Menu_Item', $data);
    }
}
