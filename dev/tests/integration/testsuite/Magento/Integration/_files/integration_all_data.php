<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$integrationService = $objectManager->get(IntegrationServiceInterface::class);

$data = [
    'name' => 'Fixture Integration',
    'email' => 'john.doe@example.com',
    'endpoint' => 'http://localhost/endpoint',
    'identity_link_url' => 'http://localhost/link',
    'all_resources' => 0,
    'status' => 1
];
$integrationService->create($data);
