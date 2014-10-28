<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

class Redirect extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Retrieve params and put javascript into iframe
     *
     * @return void
     */
    public function execute()
    {
        $redirectParams = $this->getRequest()->getParams();
        $params = array();
        if (!empty($redirectParams['success']) && isset(
            $redirectParams['x_invoice_num']
        ) && isset(
            $redirectParams['controller_action_name']
        )
        ) {
            $this->_getDirectPostSession()->unsetData('quote_id');
            $params['redirect_parent'] = $this->_objectManager->get(
                'Magento\Authorizenet\Helper\HelperInterface'
            )->getSuccessOrderUrl(
                $redirectParams
            );
        }
        if (!empty($redirectParams['error_msg'])) {
            $cancelOrder = empty($redirectParams['x_invoice_num']);
            $this->_returnCustomerQuote($cancelOrder, $redirectParams['error_msg']);
        }

        if (isset(
            $redirectParams['controller_action_name']
        ) && strpos(
            $redirectParams['controller_action_name'],
            'sales_order_'
        ) !== false
        ) {
            unset($redirectParams['controller_action_name']);
            unset($params['redirect_parent']);
        }

        $this->_coreRegistry->register('authorizenet_directpost_form_params', array_merge($params, $redirectParams));
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false)->renderLayout();
    }
}
