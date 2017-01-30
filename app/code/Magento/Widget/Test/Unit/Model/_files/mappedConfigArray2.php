<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '@' => ['type' => 'Magento\Cms\Block\Widget\Page\Link', 'module' => 'Magento_Cms'],
    'name' => 'CMS Link 2',
    'description' => 'Second Link Example',
    'parameters' => [
        'types' => [
            'type' => 'multiselect',
            'visible' => '1',
            'source_model' => 'Magento\Cms\Model\Config\Source\Page',
        ],
    ]
];
