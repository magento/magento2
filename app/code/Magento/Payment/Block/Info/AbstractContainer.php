<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Info;

/**
 * Payment information container block
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractContainer extends \Magento\Framework\View\Element\Template
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $data);
    }

    /**
     * Add payment info block to layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        if ($info = $this->getPaymentInfo()) {
            $this->setChild($this->_getInfoBlockName(), $this->_paymentData->getInfoBlock($info, $this->getLayout()));
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve info block name
     *
     * @return string|false
     * @since 2.0.0
     */
    protected function _getInfoBlockName()
    {
        if ($info = $this->getPaymentInfo()) {
            return 'payment.info.' . $info->getMethodInstance()->getCode();
        }
        return false;
    }

    /**
     * Retrieve payment info model
     *
     * @return \Magento\Payment\Model\Info|false
     * @since 2.0.0
     */
    abstract public function getPaymentInfo();

    /**
     * Declare info block template
     *
     * @param string $method
     * @param string $template
     * @return $this
     * @since 2.0.0
     */
    public function setInfoTemplate($method = '', $template = '')
    {
        if ($info = $this->getPaymentInfo()) {
            if ($info->getMethodInstance()->getCode() == $method) {
                $this->getChildBlock($this->_getInfoBlockName())->setTemplate($template);
            }
        }
        return $this;
    }
}
