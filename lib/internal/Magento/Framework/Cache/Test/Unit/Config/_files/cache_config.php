<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'types' => [
        'config' => [
            'name' => 'config',
            'translate' => 'label,description',
            'instance' => 'Magento\Framework\App\Cache\Type\Config',
            'label' => 'Configuration',
            'description' => 'Cache Description',
        ],
        'layout' => [
            'name' => 'layout',
            'translate' => 'label,description',
            'instance' => 'Magento\Framework\App\Cache\Type\Layout',
            'label' => 'Layouts',
            'description' => 'Layout building instructions',
        ],
    ]
];
