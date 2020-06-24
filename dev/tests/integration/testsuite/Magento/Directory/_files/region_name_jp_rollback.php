<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ResourceConnection $resource */
$resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
$resource->getConnection()->delete(
    $resource->getTableName('directory_country_region_name'),
    "locale='JA_jp'"
);
