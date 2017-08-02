<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Source;

/**
 * Automatic invoice create source model
 *
 * Inheritance of this class allowed as is a part of legacy implementation.
 *
 * @api
 * @since 2.0.0
 */
class Invoice implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
