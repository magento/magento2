<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Source;

/**
 * Source model for DHL Content Type
 * @since 2.0.0
 */
class Contenttype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Documents'), 'value' => \Magento\Dhl\Model\Carrier::DHL_CONTENT_TYPE_DOC],
            ['label' => __('Non documents'), 'value' => \Magento\Dhl\Model\Carrier::DHL_CONTENT_TYPE_NON_DOC]
        ];
    }
}
