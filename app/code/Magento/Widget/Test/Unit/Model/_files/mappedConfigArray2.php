<?php declare(strict_types=1);

use Magento\Cms\Block\Widget\Page\Link;
use Magento\Cms\Model\Config\Source\Page;

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
return [
    '@' => ['type' => Link::class, 'module' => 'Magento_Cms'],
    'name' => 'CMS Link 2',
    'description' => 'Second Link Example',
    'parameters' => [
        'types' => [
            'type' => 'multiselect',
            'visible' => '1',
            'source_model' => Page::class,
        ],
    ]
];
