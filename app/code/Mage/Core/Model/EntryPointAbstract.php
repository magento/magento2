<?php
/**
 * Abstract application entry point
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_Core_Model_EntryPointAbstract
{
    /**
     * Application configuration
     *
     * @var Mage_Core_Model_Config_Primary
     */
    protected $_config;

    /**
     * Application object manager
     *
     * @var Mage_Core_Model_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Mage_Core_Model_Config_Primary $config
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Mage_Core_Model_Config_Primary $config, Magento_ObjectManager $objectManager = null)
    {
        $this->_config = $config;
        $this->_objectManager = $objectManager;
    }

    /**
     * Process request by the application
     */
    public function processRequest()
    {
        $this->_init();
        $this->_processRequest();
    }

    /**
     * Initializes the entry point, so a Magento application is ready to be used
     */
    protected function _init()
    {
        $this->_initObjectManager();
        $this->_verifyDirectories();
    }

    /**
     * Initialize object manager for the application
     */
    protected function _initObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = new Mage_Core_Model_ObjectManager($this->_config);
        }

        $this->_setGlobalObjectManager();
    }

    /**
     * Set globally-available variable
     *
     * The method is isolated in order to make safe testing possible, by mocking this method in the tests.
     */
    protected function _setGlobalObjectManager()
    {
        Mage::setObjectManager($this->_objectManager);
    }

    /**
     * Verify existence and write access to the application directories
     */
    protected function _verifyDirectories()
    {
        /** @var $verification Mage_Core_Model_Dir_Verification */
        $verification = $this->_objectManager->get('Mage_Core_Model_Dir_Verification');
        $verification->createAndVerifyDirectories();
    }

    /**
     * Template method to process request according to the actual entry point rules
     */
    protected abstract function _processRequest();
}

