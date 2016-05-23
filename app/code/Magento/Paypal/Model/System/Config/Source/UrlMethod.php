<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for url method: GET/POST
 */
class UrlMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [['value' => 'GET', 'label' => 'GET'], ['value' => 'POST', 'label' => 'POST']];
    }
}
