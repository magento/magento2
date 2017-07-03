<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$configData = [
    'default' => [
        'web/test/test_value_1' => 'http://local2.test/',
        'web/test/test_value_2' => 5,
        'web/test/test_value_3' => 'value from the DB',
        'web/test/test_sensitive' => 10,
        'general/country/default' => 'GB',

        'web/test/test_sensitive1' => 'some_value1',
        'web/test/test_sensitive2' => 'some_value2',
        'web/test/test_sensitive3' => 'some_value3',
        'web/test/test_sensitive_environment4' => 'some_value4',
        'web/test/test_sensitive_environment5' => 'some_value5',
        'web/test/test_sensitive_environment6' => 'some_value6',
        'web/test/test_environment7' => 'some_value7',
        'web/test/test_environment8' => 'some_value8',
        'web/test/test_environment9' => 'some_value9',
        \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID => 2
    ],
    'stores' => [
        'default' => [
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID => 3
        ]
    ],
    'websites' => [
        'base' => [
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID => 3
        ]
    ],
];

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$configFactory = $objectManager->create(\Magento\Config\Model\Config\Factory::class);

foreach ($configData as $scope => $data) {
    if ($scope === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
        foreach ($data as $path => $value) {
            $config = $configFactory->create();
            $config->setScope($scope);
            $config->setDataByPath($path, $value);
            $config->save();
        }
    } else {
        foreach ($data as $scopeCode => $scopeData) {
            foreach ($scopeData as $path => $value) {
                $config = $configFactory->create();
                if ($scope == 'websites') {
                    $config->setWebsite($scopeCode);
                } elseif ($scope == 'stores') {
                    $config->setStore($scopeCode);
                } else {
                    $config->setScope($scope);
                }
                $config->setDataByPath($path, $value);
                $config->save();
            }
        }
    }
}
