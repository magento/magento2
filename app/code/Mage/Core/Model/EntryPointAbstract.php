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
        if (!$objectManager) {
            $definitionFactory = new Mage_Core_Model_ObjectManager_DefinitionFactory();
            $definitions =  $definitionFactory->create($config);
            $objectManager = new Mage_Core_Model_ObjectManager($definitions, $config);
        }
        $this->_objectManager = $objectManager;
        Mage::setObjectManager($objectManager);
    }

    /**
     * Process request to application
     */
    public abstract function processRequest();
}

