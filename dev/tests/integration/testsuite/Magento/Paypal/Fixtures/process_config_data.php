<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
