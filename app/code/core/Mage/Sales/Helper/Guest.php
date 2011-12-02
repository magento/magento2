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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Helper_Guest extends Mage_Core_Helper_Data
{
    /**
     * Cookie params
     */
    protected $_cookieName  = 'guest-view';
    protected $_lifeTime    = 600;

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @return bool|null
     */
    public function loadValidOrder()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/order/history'));
            return false;
        }

        $post = Mage::app()->getRequest()->getPost();

        $type           = '';
        $incrementId    = '';
        $lastName       = '';
        $email          = '';
        $zip            = '';
        $protectCode    = '';
        $errors         = false;

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('Mage_Sales_Model_Order');

        if (empty($post) && !Mage::getSingleton('Mage_Core_Model_Cookie')->get($this->_cookieName)) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type']))  {
            $type           = $post['oar_type'];
            $incrementId    = $post['oar_order_id'];
            $lastName       = $post['oar_billing_lastname'];
            $email          = $post['oar_email'];
            $zip            = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || (!in_array($type, array('email', 'zip')))
                || ($type == 'email' && empty($email)) || ($type == 'zip' && empty($zip))) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if ((strtolower($lastName) != strtolower($billingAddress->getLastname()))
                    || ($type == 'email'
                        && strtolower($email) != strtolower($billingAddress->getEmail()))
                    || ($type == 'zip'
                        && (strtolower($zip) != strtolower($billingAddress->getPostcode())))
                ) {
                    $errors = true;
                }
            } else {
                $errors = true;
            }

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode());
                Mage::getSingleton('Mage_Core_Model_Cookie')->set($this->_cookieName, $toCookie, $this->_lifeTime, '/');
            }
        } elseif (Mage::getSingleton('Mage_Core_Model_Cookie')->get($this->_cookieName)) {
            $fromCookie     = Mage::getSingleton('Mage_Core_Model_Cookie')->get($this->_cookieName);
            $protectCode    = base64_decode($fromCookie);

            if (!empty($protectCode)) {
                $order->loadByAttribute('protect_code', $protectCode);

                Mage::getSingleton('Mage_Core_Model_Cookie')->renew($this->_cookieName, $this->_lifeTime, '/');
            } else {
                $errors = true;
            }
        }

        if (!$errors && $order->getId()) {
            Mage::register('current_order', $order);
            return true;
        }

        Mage::getSingleton('Mage_Core_Model_Session')->addError(
            $this->__('Entered data is incorrect. Please try again.')
        );
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
        return false;
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @param  Mage_Core_Controller_Front_Action $controller
     */
    public function getBreadcrumbs($controller)
    {
        $breadcrumbs = $controller->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            array(
                'label' => Mage::helper('Mage_Cms_Helper_Data')->__('Home'),
                'title' => Mage::helper('Mage_Cms_Helper_Data')->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            )
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            array(
                'label' => 'Order Information',
                'title' => 'Order Information'
            )
        );
    }

}
