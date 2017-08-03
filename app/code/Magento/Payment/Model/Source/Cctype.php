<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Source;

/**
 * Payment CC Types Source Model
 *
 * Inheritance of this class allowed as is a part of legacy implementation.
 *
 * @api
 * @since 2.0.0
 */
class Cctype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Allowed CC types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allowedTypes = [];

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     * @since 2.0.0
     */
    protected $_paymentConfig;

    /**
     * Config
     *
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Payment\Model\Config $paymentConfig)
    {
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * Return allowed cc types for current method
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllowedTypes()
    {
        return $this->_allowedTypes;
    }

    /**
     * Setter for allowed types
     *
     * @param array $values
     * @return $this
     * @since 2.0.0
     */
    public function setAllowedTypes(array $values)
    {
        $this->_allowedTypes = $values;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        /**
         * making filter by allowed cards
         */
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed) || !count($allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
