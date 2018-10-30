<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Helper\Product;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$configData = [
    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
        '' => [
            Product::XML_PATH_PRODUCT_URL_USE_CATEGORY => '0',
        ],
    ],
];

$objectManager = Bootstrap::getObjectManager();
/** @var Factory $configFactory */
$configFactory = $objectManager->create(Factory::class);

foreach ($configData as $scope => $data) {
    foreach ($data as $scopeCode => $scopeData) {
        foreach ($scopeData as $path => $value) {
            $config = $configFactory->create();
            $config->setScope($scope);
            $config->setDataByPath($path, $value);
            $config->save();
        }
    }
}
