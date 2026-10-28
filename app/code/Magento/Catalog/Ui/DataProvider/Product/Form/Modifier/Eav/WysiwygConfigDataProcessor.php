<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

/**
 * Add data to related to $meta['arguments']['data']['config']['wysiwygConfigData']
 */
class WysiwygConfigDataProcessor implements WysiwygConfigDataProcessorInterface
{
    /**
     * Build WYSIWYG config data for a product attribute.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return array
     */
    public function process(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $wysiwygConfigData = [
            'add_variables' => false,
            'add_widgets' => false,
            'add_directives' => true,
            'use_container' => true,
            'container_class' => 'hor-scroll',
        ];

        if ((string)$attribute->getBackendType() === 'text' && (string)$attribute->getBackendTable() !== '') {
            $wysiwygConfigData['utf8mb4Target'] = [
                'table' => (string)$attribute->getBackendTable(),
                'column' => 'value',
            ];
        }

        return $wysiwygConfigData;
    }
}
