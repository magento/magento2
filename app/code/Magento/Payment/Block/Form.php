<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Payment\Model\MethodInterface;

/**
 * Payment method form base block
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve payment method model
     *
     * @return MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethod()
    {
        $method = $this->getData('method');

        if (!$method instanceof MethodInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot retrieve the payment method model object.')
            );
        }
        return $method;
    }

    /**
     * Sets payment method instance to form
     *
     * @param MethodInterface $method
     * @return $this
     */
    public function setMethod(MethodInterface $method)
    {
        $this->setData('method', $method);
        return $this;
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
