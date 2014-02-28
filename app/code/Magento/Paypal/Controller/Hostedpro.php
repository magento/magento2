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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Controller;

/**
 * Hosted Pro Checkout Controller
 *
 * @category   Magento
 * @package    Magento_Paypal
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Hostedpro extends \Magento\App\Action\Action
{
    /**
     * When a customer return to website from gateway.
     *
     * @return void
     */
    public function returnAction()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');;
        //TODO: some actions with order
        if ($session->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }

    /**
     * When a customer cancel payment from gateway.
     *
     * @return void
     */
    public function cancelAction()
    {
        $this->_view->loadLayout(false);
        $gotoSection = $this->_cancelPayment();
        $redirectBlock = $this->_view->getLayout()->getBlock('hosted.pro.iframe');
        $redirectBlock->setGotoSection($gotoSection);
        //TODO: clarify return logic whether customer will be returned in iframe or in parent window
        $this->_view->renderLayout();
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $gotoSection = false;
        $helper = $this->_objectManager->get('Magento\Paypal\Helper\Checkout');
        $helper->cancelCurrentOrder($errorMsg);
        if ($helper->restoreQuote()) {
            $gotoSection = 'payment';
        }

        return $gotoSection;
    }
}
