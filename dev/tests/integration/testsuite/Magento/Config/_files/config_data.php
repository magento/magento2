<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

$configData = [
    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
        '' => [
            'web/test/test_value_1' => 'http://default.test/',
            'web/test/test_value_2' => 'someValue',
            'web/test/test_value_3' => 100,
        ]
    ],
    ScopeInterface::SCOPE_WEBSITES => [
        'base' => [
            'web/test/test_value_1' => 'http://website.test/',
            'web/test/test_value_2' => 'someWebsiteValue',
            'web/test/test_value_3' => 101,
        ]
    ]
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

            $config->setDataByPath($path, $value);
            $config->save();
        }
    }
}
