<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Source\Method;

/**
 * Source model for DHL shipping methods
 */
abstract class AbstractMethod extends \Magento\Dhl\Model\Source\Method\Generic
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     */
    protected $_contentType;

    /**
     * Show 'none' in methods list or not;
     *
     * @var bool
     */
    protected $_noneMethod = false;

    /**
     * {@inheritdoc}
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
