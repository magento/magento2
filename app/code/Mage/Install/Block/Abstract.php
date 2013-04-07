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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Abstract installation block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Install_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * Retrieve installer model
     *
     * @return Mage_Install_Model_Installer
     */
    public function getInstaller()
    {
        return Mage::getSingleton('Mage_Install_Model_Installer');
    }
    
    /**
     * Retrieve wizard model
     *
     * @return Mage_Install_Model_Wizard
     */
    public function getWizard()
    {
        return Mage::getSingleton('Mage_Install_Model_Wizard');
    }
    
    /**
     * Retrieve current installation step
     *
     * @return Varien_Object
     */
    public function getCurrentStep()
    {
        return $this->getWizard()->getStepByRequest($this->getRequest());
    }
}
