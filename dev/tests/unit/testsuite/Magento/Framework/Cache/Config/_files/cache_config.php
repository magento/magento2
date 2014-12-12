<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            'description' => 'Layout building instructions.',
        ],
    ]
];
