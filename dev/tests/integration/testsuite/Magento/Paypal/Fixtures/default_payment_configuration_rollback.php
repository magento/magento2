<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

$configData = [
    'payment/payflowpro/partner',
    'payment/payflowpro/vendor',
    'payment/payflowpro/user',
    'payment/payflowpro/pwd',
];
/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
foreach ($configData as $path) {
    $configWriter->delete($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
}
