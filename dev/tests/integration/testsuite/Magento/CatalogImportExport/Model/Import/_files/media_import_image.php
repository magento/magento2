<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
$mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\Filesystem'
)->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
);
$mediaDirectory->create('import/m/a');
$dirPath = $mediaDirectory->getAbsolutePath('import');
copy(__DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg', "{$dirPath}/m/a/magento_image.jpg");
