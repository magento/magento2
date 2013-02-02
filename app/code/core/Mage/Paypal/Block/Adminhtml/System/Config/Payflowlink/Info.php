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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Renderer for Payflow Link information
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Block_Adminhtml_System_Config_Payflowlink_Info
    extends Mage_Core_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'system/config/payflowlink/info.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * Get frontend url
     *
     * @param string $routePath
     * @return strting
     */
    public function getFrontendUrl($routePath)
    {
        if ($this->getRequest()->getParam('website')) {
            $website = Mage::getModel('Mage_Core_Model_Website')->load($this->getRequest()->getParam('website'));
            $secure = Mage::getStoreConfigFlag(
                Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT,
                $website->getDefaultStore()
            );
            $path = $secure ?
                Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL :
                Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL;
            $websiteUrl = Mage::getStoreConfig($path, $website->getDefaultStore());
        } else {
            $secure = Mage::getStoreConfigFlag(
                Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT
            );
            $websiteUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
        }

        return $websiteUrl . $routePath;
    }
}
