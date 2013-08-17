<?php
/**
 * Abstract helper context
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Helper_Context implements Magento_ObjectManager_ContextInterface
{
    /**
     * @var Mage_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Mage_Core_Model_ModuleManager
     */
    protected $_moduleManager;

    /**
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_ModuleManager $moduleManager
     */
    public function __construct(Mage_Core_Model_Translate $translator, Mage_Core_Model_ModuleManager $moduleManager)
    {
        $this->_translator = $translator;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return Mage_Core_Model_Translate
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * @return Mage_Core_Model_ModuleManager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }
}
