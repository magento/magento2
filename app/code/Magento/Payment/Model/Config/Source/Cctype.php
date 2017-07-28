<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config\Source;

/**
 * Class \Magento\Payment\Model\Config\Source\Cctype
 *
 * @since 2.0.0
 */
class Cctype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     * @since 2.0.0
     */
    protected $_paymentConfig;

    /**
     * Construct
     *
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Payment\Model\Config $paymentConfig)
    {
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
