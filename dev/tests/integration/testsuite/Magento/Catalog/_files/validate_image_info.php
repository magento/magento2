<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->create('Magento\Framework\Filesystem');

/** @var $tmpDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
$tmpDirectory->create('var/tmp');

$targetTmpFilePath = $tmpDirectory->getAbsolutePath('var/tmp/magento_small_image.jpg');
copy(__DIR__ . '/magento_small_image.jpg', $targetTmpFilePath);
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
