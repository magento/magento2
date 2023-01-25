<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    // the following modules must be disabled when Live Search is used
    // so core modules must not be dependent on them
    'Magento\LiveSearch' => [
        'Magento\Elasticsearch',
        'Magento\Elasticsearch7',
        'Magento\Elasticsearch8',
        'Magento\OpenSearch'
    ],
];
