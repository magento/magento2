<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

            $paymentMethod->setStore($this->_sessionQuote->getQuote()->getStoreId());
            $paymentMethod->cancelPartialAuthorization($this->_sessionQuote->getQuote()->getPayment());

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
