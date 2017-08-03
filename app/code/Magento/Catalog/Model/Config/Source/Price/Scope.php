<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Price;

/**
 * Class \Magento\Catalog\Model\Config\Source\Price\Scope
 *
 * @since 2.0.0
 */
class Scope implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [['value' => '0', 'label' => __('Global')], ['value' => '1', 'label' => __('Website')]];
    }
}
