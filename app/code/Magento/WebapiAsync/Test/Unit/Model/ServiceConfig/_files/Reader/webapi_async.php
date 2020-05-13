<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
use Magento\Customer\Api\CustomerRepositoryInterface;

return [
    'services' => [
        CustomerRepositoryInterface::class => [
            'methods' => [
                'getById' => [
                    'synchronousInvocationOnly' => true,
                ],
                'save' => [
                    'synchronousInvocationOnly' => true,
                ],
                'get' => [
                    'synchronousInvocationOnly' => false,
                ],
            ],
        ],
    ],
    'routes' => [
        'asyncProducts' => ['POST' => 'async/bulk/V1/products'],
        'asyncBulkCmsPages' => ['POST' => 'async/bulk/V1/cmsPage'],
        'asyncCustomers' => ['POST' => 'async/V1/customers']
    ]
];
