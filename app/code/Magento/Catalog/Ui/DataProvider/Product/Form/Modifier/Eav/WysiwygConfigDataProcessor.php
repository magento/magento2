<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

/**
 * Add data to related to $meta['arguments']['data']['config']['wysiwygConfigData']
 */
class WysiwygConfigDataProcessor implements WysiwygConfigDataProcessorInterface
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        return [
            'add_variables' => false,
            'add_widgets' => false,
            'add_directives' => true,
            'use_container' => true,
            'container_class' => 'hor-scroll',
        ];
    }
}
