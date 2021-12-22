<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$integrationService = $objectManager->get(IntegrationServiceInterface::class);

$data = [
    'name' => 'Fixture Integration',
    'email' => 'john.doe@example.com',
    'endpoint' => 'https://example.com/endpoint',
    'identity_link_url' => 'https://example.com/link',
    'all_resources' => 0,
];
$integrationService->create($data);
