<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Filters\Options;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Used in filter options
 */
class UsedIn implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            'cms_page' => [
                'value' => 'cms_page',
                'label' => 'Pages'
            ],
            'catalog_category' => [
                'value' => 'catalog_category',
                'label' => 'Categories'
            ],
            'cms_block' => [
                'value' => 'cms_block',
                'label' => 'Blocks'
            ],
            'catalog_product' => [
                'value' => 'catalog_product',
                'label' => 'Products'
            ],
            'not_used' => [
                'value' => 'not_used',
                'label' => 'Not used anywhere'
            ]
        ];
    }
}
