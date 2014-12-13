<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Block\Directpost;

/**
 * DirectPost form block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var string
     */
    protected $_template = 'directpost/info.phtml';

    /**
     * @var \Magento\Authorizenet\Model\Directpost
     */
    protected $_model;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $_checkoutModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Authorizenet\Model\Directpost $model
     * @param \Magento\Checkout\Model\Type\Onepage $checkoutModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Authorizenet\Model\Directpost $model,
        \Magento\Checkout\Model\Type\Onepage $checkoutModel,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->_model = $model;
        $this->_checkoutModel = $checkoutModel;
    }

    /**
     * Render block HTML
     * If method is not directpost - nothing to return
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getMethod()->getCode() == $this->_model->getCode() ? parent::_toHtml() : '';
    }

    /**
     * Set method info
     *
     * @return $this
     */
    public function setMethodInfo()
    {
        $payment = $this->_checkoutModel->getQuote()->getPayment();
        $this->setMethod($payment->getMethodInstance());
        return $this;
    }

    /**
     * Get type of request
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return $this->getRequest()->getParam('isAjax');
    }
}
