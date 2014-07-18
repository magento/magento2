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
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Payment;

class Cancel extends \Magento\Backend\App\Action
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote
    ) {
        $this->_sessionQuote = $sessionQuote;
        parent::__construct($context);
    }

    /**
     * Cancel active partial authorizations
     *
     * @return void
     */
    public function execute()
    {
        $result['success'] = false;
        try {
            $paymentMethod = $this->_objectManager->get(
                'Magento\Payment\Helper\Data'
            )->getMethodInstance(
                \Magento\Authorizenet\Model\Authorizenet::METHOD_CODE
            );

            if ($paymentMethod) {
                $paymentMethod->setStore($this->_sessionQuote->getQuote()->getStoreId());
                $paymentMethod->cancelPartialAuthorization($this->_sessionQuote->getQuote()->getPayment());
            }

            $result['success'] = true;
            $result['update_html'] = $this->_objectManager->get(
                'Magento\Authorizenet\Helper\Data'
            )->getPaymentMethodsHtml(
                $this->_view
            );
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $result['error_message'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $result['error_message'] = __('Something went wrong canceling the transactions.');
        }

        $this->_sessionQuote->getQuote()->getPayment()->save();
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
