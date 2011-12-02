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
 * @package     Mage_Authorizenet
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Authorizenet Data Helper
 *
 * @category   Mage
 * @package    Mage_Authorizenet
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Authorizenet_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return URL for admin area
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getAdminUrl($route, $params)
    {
        return Mage::getModel('Mage_Adminhtml_Model_Url')->getUrl($route, $params);
    }

    /**
     * Set secure url checkout is secure for current store.
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        $params['_type'] = Mage_Core_Model_Store::URL_TYPE_LINK;
        if (isset($params['is_secure'])) {
            $params['_secure'] = (bool)$params['is_secure'];
        } elseif (Mage::app()->getStore()->isCurrentlySecure()) {
            $params['_secure'] = true;
        }
        return parent::_getUrl($route, $params);
    }

    /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {
        $route = array();
        switch ($controller) {
            case 'onepage':
                $route['action'] = 'saveOrder';
                $route['controller'] = 'onepage';
                $route['module'] = 'checkout';
                break;

            case 'sales_order_create':
            case 'sales_order_edit':
                $route['action'] = 'save';
                $route['controller'] = 'sales_order_create';
                $route['module'] = 'admin';
                break;

            default:
                break;
        }

        return $route;
    }

    /**
     * Retrieve redirect ifrmae url
     *
     * @param array params
     * @return string
     */
    public function getRedirectIframeUrl($params)
    {
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'authorizenet/directpost_payment/redirect';
                break;

            case 'sales_order_create':
            case 'sales_order_edit':
                $route = 'adminhtml/authorizenet_directpost_payment/redirect';
                break;

            default:
                $route = 'authorizenet/directpost_payment/redirect';
                break;
        }

        return $this->_getUrl($route, $params);
    }

    /**
     * Retrieve place order url on front
     *
     * @return  string
     */
    public function getPlaceOrderFrontUrl()
    {
        return $this->_getUrl('authorizenet/directpost_payment/place');
    }

    /**
     * Retrieve place order url in admin
     *
     * @return  string
     */
    public function getPlaceOrderAdminUrl()
    {
        return $this->getAdminUrl('*/authorizenet_directpost_payment/place', array());
    }

    /**
     * Retrieve place order url
     *
     * @param array params
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        $param = array();
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'checkout/onepage/success';
                break;

            case 'sales_order_create':
            case 'sales_order_edit':
                $route = 'adminhtml/sales_order/view';
                $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($params['x_invoice_num']);
                $param['order_id'] = $order->getId();
                return $this->getAdminUrl($route, $param);

            default :
                $route = 'checkout/onepage/success';
                break;
        }

        return $this->_getUrl($route, $param);
    }

    /**
     * Get controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }

    /**
     * Update all child and parent order's edit increment numbers.
     * Needed for Admin area.
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function updateOrderEditIncrements(Mage_Sales_Model_Order $order)
    {
        if ($order->getId() && $order->getOriginalIncrementId()) {
            $collection = $order->getCollection();
            $quotedIncrId = $collection->getConnection()->quote($order->getOriginalIncrementId());
            $collection->getSelect()->where(
                "original_increment_id = {$quotedIncrId} OR increment_id = {$quotedIncrId}"
            );

            foreach ($collection as $orderToUpdate) {
                $orderToUpdate->setEditIncrement($order->getEditIncrement());
                $orderToUpdate->save();
            }
        }
    }
}
