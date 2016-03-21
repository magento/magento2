<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config\Source;

class Cctype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * Construct
     *
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(\Magento\Payment\Model\Config $paymentConfig)
    {
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            $options[] = ['value' => $code, 'label' => $name];
        }

        return $options;
    }
}
