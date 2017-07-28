<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

/**
 * Product option types mode source
 * @since 2.0.0
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Product Option Config
     *
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     * @since 2.0.0
     */
    protected $_productOptionConfig;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig)
    {
        $this->_productOptionConfig = $productOptionConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $groups = [['value' => '', 'label' => __('-- Please select --')]];

        foreach ($this->_productOptionConfig->getAll() as $option) {
            $types = [];
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $types[] = ['label' => __($type['label']), 'value' => $type['name']];
            }
            if (count($types)) {
                $groups[] = ['label' => __($option['label']), 'value' => $types, 'optgroup-name' => $option['label']];
            }
        }

        return $groups;
    }
}
