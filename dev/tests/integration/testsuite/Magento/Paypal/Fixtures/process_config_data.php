<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;

$processConfigData = function (Config $config, array $data) {
    foreach ($data as $key => $value) {
        $config->setDataByPath($key, $value);
        $config->save();
    }
};

$deleteConfigData = function (WriterInterface $writer, array $configData, string $scope, int $scopeId) {
    foreach ($configData as $path) {
        $writer->delete($path, $scope, $scopeId);
    }
};
