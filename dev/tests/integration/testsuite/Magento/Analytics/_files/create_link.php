<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var $fileInfoManager \Magento\Analytics\Model\FileInfoManager
 */
$fileInfoManager = $objectManager->create(\Magento\Analytics\Model\FileInfoManager::class);

/**
 * @var $fileInfo \Magento\Analytics\Model\FileInfo
 */
$fileInfo = $objectManager->create(
    \Magento\Analytics\Model\FileInfo::class,
    ['path' => 'analytics/jsldjsfdkldf/data.tgz', 'initializationVector' => 'binaryDataisdodssds8iui']
);

$fileInfoManager->save($fileInfo);
