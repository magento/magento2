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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Controller_Varien_Router_Admin extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Fetch default path
     */
    public function fetchDefault()
    {
        // set defaults
        $d = explode('/', $this->_getDefaultPath());
        $this->getFront()->setDefault(array(
            'module'     => !empty($d[0]) ? $d[0] : '',
            'controller' => !empty($d[1]) ? $d[1] : 'index',
            'action'     => !empty($d[2]) ? $d[2] : 'index'
        ));
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return (string)Mage::getConfig()->getNode('default/web/default/admin');
    }

    /**
     * dummy call to pass through checking
     *
     * @return unknown
     */
    protected function _beforeModuleMatch()
    {
        return true;
    }

    /**
     * checking if we installed or not and doing redirect
     *
     * @return bool
     */
    protected function _afterModuleMatch()
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        return true;
    }

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Check whether url should be secure
     *
     * @param mixed $path
     * @return bool
     */
    protected function _shouldBeSecure($path)
    {
        return substr((string)Mage::getConfig()->getNode('default/web/unsecure/base_url'),0,5)==='https'
            || Mage::getStoreConfigFlag('web/secure/use_in_adminhtml', Mage_Core_Model_App::ADMIN_STORE_ID)
            && substr((string)Mage::getConfig()->getNode('default/web/secure/base_url'),0,5)==='https';
    }

    /**
     * Retrieve current secure url
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        return Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Emulate custom admin url
     *
     * @param string $configArea
     * @param bool $useRouterName
     */
    public function collectRoutes($configArea, $useRouterName)
    {
        if ((string)Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_USE_CUSTOM_ADMIN_PATH)) {
            $customUrl = (string)Mage::getConfig()->getNode(Mage_Adminhtml_Helper_Data::XML_PATH_CUSTOM_ADMIN_PATH);
            $xmlPath = Mage_Adminhtml_Helper_Data::XML_PATH_ADMINHTML_ROUTER_FRONTNAME;
            if ((string)Mage::getConfig()->getNode($xmlPath) != $customUrl) {
                Mage::getConfig()->setNode($xmlPath, $customUrl, true);
            }
        }
        parent::collectRoutes($configArea, $useRouterName);
    }
}
