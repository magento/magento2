<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

/**
 * Payment method form base block
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve payment method model
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Model\Exception
     */
    public function getMethod()
    {
        $method = $this->getData('method');

        if (!$method instanceof \Magento\Payment\Model\MethodInterface) {
            throw new \Magento\Framework\Model\Exception(__('We cannot retrieve the payment method model object.'));
        }
        return $method;
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->getMethod()->getCode();
    }

    /**
     * Retrieve field value data from payment info object
     *
     * @param   string $field
     * @return  mixed
     */
    public function getInfoData($field)
    {
        return $this->escapeHtml($this->getMethod()->getInfoInstance()->getData($field));
    }
}
