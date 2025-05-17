<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    // the following modules must be disabled when Live Search is used
    // so core modules must not be dependent on them
    'Magento\LiveSearch' => [
        'Magento\Elasticsearch',
        'Magento\Elasticsearch8',
        'Magento\OpenSearch'
    ],
];
