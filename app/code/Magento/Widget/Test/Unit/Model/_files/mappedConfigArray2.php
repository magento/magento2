<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '@' => ['type' => 'Magento\GiftRegistry\Block\Search\Widget\Form', 'module' => 'Magento_GiftRegistry'],
    'name' => 'Gift Registry Search',
    'description' => 'Gift Registry Quick Search Form',
    'parameters' => [
        'types' => [
            'type' => 'multiselect',
            'visible' => '1',
            'source_model' => 'Magento\GiftRegistry\Model\Source\Search',
        ],
    ]
];
