<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

$configData = [
    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
        '' => [
            'web/test/test_value_1' => 'value1.db.default.test',
            'web/test/test_value_2' => 'value2.db.default.test',
            'web/test2/test_value_3' => 'value3.db.default.test',
            'web/test2/test_value_4' => 'value4.db.default.test',
            'carriers/fedex/account' => 'value5.db.hashed.value',
            'paypal/fetch_reports/ftp_password' => 'value6.db.hashed.value',
        ]
    ],
    ScopeInterface::SCOPE_WEBSITES => [
        'base' => [
            'web/test/test_value_1' => 'value1.db.website_base.test',
            'web/test/test_value_2' => 'value2.db.website_base.test',
            'web/test2/test_value_3' => 'value3.db.website_base.test',
            'web/test2/test_value_4' => 'value4.db.website_base.test',
        ]
    ],
    ScopeInterface::SCOPE_STORES => [
        'default' => [
            'web/test/test_value_1' => 'value1.db.store_default.test',
            'web/test/test_value_2' => 'value2.db.store_default.test',
            'web/test2/test_value_3' => 'value3.db.store_default.test',
            'web/test2/test_value_4' => 'value4.db.store_default.test',
        ]
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

            if ($scope == ScopeInterface::SCOPE_WEBSITES) {
                $config->setWebsite($scopeCode);
            }

            if ($scope == ScopeInterface::SCOPE_STORES) {
                $config->setStore($scopeCode);
            }

            $config->setDataByPath($path, $value);
            $config->save();
        }
    }
}
