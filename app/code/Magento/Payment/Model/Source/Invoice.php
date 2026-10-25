<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Model\Source;

/**
 * Automatic invoice create source model
 *
 * Inheritance of this class allowed as is a part of legacy implementation.
 *
 * @api
 * @since 100.0.2
 */
class Invoice implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Yes'),
            ],
            ['value' => '', 'label' => __('No')]
        ];
    }
}
