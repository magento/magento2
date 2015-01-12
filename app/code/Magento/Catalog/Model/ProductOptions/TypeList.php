<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductOptions;

class TypeList implements \Magento\Catalog\Api\ProductCustomOptionTypeListInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder
     */
    protected $builder;

    /**
     * @param Config $config
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder $builder
     */
    public function __construct(
        Config $config,
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder $builder
    ) {
        $this->config = $config;
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $output = [];
        foreach ($this->config->getAll() as $option) {
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $itemData = [
                    'label' => __($type['label']),
                    'code' => $type['name'],
                    'group' => __($option['label']),
                ];
                $output[] = $this->builder->populateWithArray($itemData)->create();
            }
        }
        return $output;
    }
}
