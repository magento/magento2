<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$configData = [
    'default' => [
        '' => [
            'web/test/test_value_1' => 'http://local2.test/',
            'web/test/test_value_2' => 5,
            'web/test/test_sensitive' => 10,

            'web/test/test_sensitive1' => 'some_value1',
            'web/test/test_sensitive2' => 'some_value2',
            'web/test/test_sensitive3' => 'some_value3',
            'web/test/test_sensitive_environment4' => 'some_value4',
            'web/test/test_sensitive_environment5' => 'some_value5',
            'web/test/test_sensitive_environment6' => 'some_value6',
            'web/test/test_environment7' => 'some_value7',
            'web/test/test_environment8' => 'some_value8',
            'web/test/test_environment9' => 'some_value9',
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
