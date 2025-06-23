<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Config\Source\Price;

class Scope implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [['value' => '0', 'label' => __('Global')], ['value' => '1', 'label' => __('Website')]];
    }
}
