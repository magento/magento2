<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$configData = [
    'default' => [
        '' => [
            'web/test/test_value_1' => 'http://local2.test/',
            'web/test/test_value_2' => 5,
            'web/test/test_sensitive' => 10,
        ]
    ],
];

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$configFactory = $objectManager->create(\Magento\Config\Model\Config\Factory::class);

foreach ($configData as $scope => $data) {
    foreach ($data as $scopeCode => $scopeData) {
        foreach ($scopeData as $path => $value) {
            $config = $configFactory->create();
            $config->setCope($scope);

            if ($scopeCode) {
                $config->setScopeCode($scopeCode);
            }

            $config->setDataByPath($path, $value);
            $config->save();
        }
    }
}
