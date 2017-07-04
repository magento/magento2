<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme\Collection;

$objectManager = Bootstrap::getObjectManager();
$configFactory = $objectManager->create(Factory::class);
$themeList = $objectManager->create(Collection::class);

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
        DesignInterface::XML_PATH_THEME_ID => $themeList->getThemeByFullPath('frontend/Magento/blank')->getThemeId()
    ],
    'stores' => [
        'default' => [
            DesignInterface::XML_PATH_THEME_ID => $themeList->getThemeByFullPath('frontend/Magento/luma')->getThemeId()
        ]
    ],
    'websites' => [
        'base' => [
            DesignInterface::XML_PATH_THEME_ID => $themeList->getThemeByFullPath('frontend/Magento/luma')->getThemeId()
        ]
    ],
];

foreach ($configData as $scope => $data) {
    if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
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
