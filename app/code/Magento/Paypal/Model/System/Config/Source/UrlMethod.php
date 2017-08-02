<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for url method: GET/POST
 * @since 2.0.0
 */
class UrlMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [['value' => 'GET', 'label' => 'GET'], ['value' => 'POST', 'label' => 'POST']];
    }
}
