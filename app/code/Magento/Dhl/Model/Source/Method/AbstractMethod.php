<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Source\Method;

/**
 * Source model for DHL shipping methods
 * @since 2.0.0
 */
abstract class AbstractMethod extends \Magento\Dhl\Model\Source\Method\Generic
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     * @since 2.0.0
     */
    protected $_contentType;

    /**
     * Show 'none' in methods list or not;
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_noneMethod = false;

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        /* @var $carrierModel \Magento\Dhl\Model\Carrier */
        $carrierModel = $this->_shippingDhl;
        $dhlProducts = $carrierModel->getDhlProducts($this->_contentType);

        $options = [];
        foreach ($dhlProducts as $code => $title) {
            $options[] = ['value' => $code, 'label' => $title];
        }

        if ($this->_noneMethod) {
            array_unshift($options, ['value' => '', 'label' => __('None')]);
        }

        return $options;
    }
}
