<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
return [
    'reorder_sidebar' => [
        'name_in_layout' => 'sale.reorder.sidebar',
        'class' => \Magento\PersistentHistory\Model\Observer::class,
        'method' => 'initReorderSidebar',
        'block_type' => \Magento\Sales\Block\Reorder\Sidebar::class,
    ],
    'viewed_products' => [
        'name_in_layout' => 'left.reports.product.viewed',
        'class' => \Magento\PersistentHistory\Model\Observer::class,
        'method' => 'emulateViewedProductsBlock',
        'block_type' => \Magento\Sales\Block\Reorder\Sidebar::class,
    ]
];
