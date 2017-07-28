<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Payment\Model\MethodInterface;

/**
 * Payment method form base block
 *
 * @api
 * @since 2.0.0
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve payment method model
     *
     * @return MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getInfoData($field)
    {
        return $this->escapeHtml($this->getMethod()->getInfoInstance()->getData($field));
    }
}
