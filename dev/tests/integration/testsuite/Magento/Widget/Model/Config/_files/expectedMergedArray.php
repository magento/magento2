<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'new_products' => [
        '@' => ['type' => \Magento\Sales\Block\Widget\Guest\Form::class],
        'is_email_compatible' => '1',
        'placeholder_image' => 'Magento_Catalog::images/product_widget_new.png',
        'name' => 'Orders and Returns',
        'description' => 'Orders and Returns Search Form',
        'parameters' => [
            'display_type' => [
                'type' => 'select',
                'value' => 'all_products',
                'values' => [
                    'default' => ['value' => 'all_products', 'label' => 'All products'],
                    'item' => ['value' => 'new_products', 'label' => 'New products'],
                ],
                'visible' => '1',
                'required' => '1',
                'label' => 'Display Type',
                'description' => 'All products - recently added products, New products - products marked as new',
            ],
            'show_pager' => [
                'source_model' => "Magento\Config\Model\Config\Source\Yesno",
                'type' => 'select',
                'visible' => '1',
                'label' => 'Display Page Control',
            ],
            'products_per_page' => [
                'type' => 'text',
                'value' => '5',
                'visible' => '1',
                'required' => '1',
                'label' => 'Number of Products per Page',
                'depends' => ['show_pager' => ['value' => '1']],
            ],
            'products_count' => [
                'type' => 'text',
                'value' => '10',
                'visible' => '1',
                'required' => '1',
                'label' => 'Number of Products to Display',
            ],
            'template' => [
                'type' => 'select',
                'value' => 'product/widget/new/content/new_grid.phtml',
                'values' => [
                    'default' => [
                        'value' => 'product/widget/new/content/new_grid.phtml',
                        'label' => 'New Products Grid Template',
                    ],
                    'list' => [
                        'value' => 'product/widget/new/content/new_list.phtml',
                        'label' => 'New Products List Template',
                    ],
                    'list_default' => [
                        'value' => 'product/widget/new/column/new_default_list.phtml',
                        'label' => 'New Products Images and Names Template',
                    ],
                    'list_names' => [
                        'value' => 'product/widget/new/column/new_names_list.phtml',
                        'label' => 'New Products Names Only Template',
                    ],
                    'list_images' => [
                        'value' => 'product/widget/new/column/new_images_list.phtml',
                        'label' => 'New Products Images Only Template',
                    ],
                    'default_template' => ['value' => 'widget/guest/form.phtml', 'label' => 'Default Template'],
                ],
                'visible' => '0',
                'required' => '1',
                'label' => 'Template',
            ],
            'cache_lifetime' => [
                'type' => 'text',
                'visible' => '1',
                'label' => 'Cache Lifetime (Seconds)',
                'description' => "86400 by default, if not set. To refresh instantly, clear the Blocks HTML
                    Output cache.
                ",
            ],
            'title' => ['type' => 'text', 'visible' => '0', 'label' => 'Anchor Custom Title'],
        ],
        'supported_containers' => [
            [
                'container_name' => 'left',
                'template' => [
                    'default' => 'default_template',
                    'names_only' => 'list_names',
                    'images_only' => 'list_images',
                ],
            ],
            ['container_name' => 'content', 'template' => ['grid' => 'default', 'list' => 'list']],
            [
                'container_name' => 'right',
                'template' => [
                    'default' => 'default_template',
                    'names_only' => 'list_names',
                    'images_only' => 'list_images',
                ]
            ],
        ],
    ]
];
