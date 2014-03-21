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

use Magento\Sales\Model\Order;

/**
 * Paypal Standard Checkout Controller
 *
 * @category   Magento
 * @package    Magento_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Standard extends \Magento\App\Action\Action
{
    /**
     * Order instance
     *
     * @var Order
     */
    protected $_order;

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Send expire header to ajax response
     *
     * @return void
     */
    protected function _expireAjax()
    {
        if (!$this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with paypal strandard order transaction information
     *
     * @return \Magento\Paypal\Model\Standard
     */
    public function getStandard()
    {
        return $this->_objectManager->get('Magento\Paypal\Model\Standard');
    }

    /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     * @return void
     */
    public function redirectAction()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setPaypalStandardQuoteId($session->getQuoteId());
        $this->_view->loadLayout(false)->renderLayout();
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    /**
     * When a customer cancel payment from paypal.
     *
     * @return void
     */
    public function cancelAction()
    {
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        if ($session->getLastRealOrderId()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create(
                'Magento\Sales\Model\Order'
            )->loadByIncrementId(
                $session->getLastRealOrderId()
            );
            if ($order->getId()) {
                $order->cancel()->save();
            }
            $session->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * When paypal returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     *
     * @return void
     */
    public function successAction()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        $session->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }
}
