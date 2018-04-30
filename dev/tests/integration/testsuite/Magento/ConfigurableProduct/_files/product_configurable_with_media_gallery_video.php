<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/product_configurable.php';

/**
 * @var \Magento\TestFramework\ObjectManager $objectManager
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory
 */

$mediaGalleryEntryFactory = $objectManager->get(
    \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory::class
);

/**
 * @var \Magento\Framework\Api\Data\ImageContentInterfaceFactory $imageContentFactory
 */
$imageContentFactory = $objectManager->get(\Magento\Framework\Api\Data\ImageContentInterfaceFactory::class);
$imageContent = $imageContentFactory->create();
$testImagePath = __DIR__ .'/magento_image.jpg';
$imageContent->setBase64EncodedData(base64_encode(file_get_contents($testImagePath)));
$imageContent->setType("image/jpeg");
$imageContent->setName("1.jpg");

$video = $mediaGalleryEntryFactory->create();
$video->setDisabled(false);
//$video->setFile('1.png');
$video->setFile('1.jpg');
$video->setLabel('Video Label');
$video->setMediaType('external-video');
$video->setPosition(2);
$video->setContent($imageContent);

/**
 * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory $mediaGalleryEntryExtensionFactory
 */
$mediaGalleryEntryExtensionFactory = $objectManager->get(
    \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory::class
);
$mediaGalleryEntryExtension = $mediaGalleryEntryExtensionFactory->create();

/**
 * @var \Magento\Framework\Api\Data\VideoContentInterfaceFactory $videoContentFactory
 */
$videoContentFactory = $objectManager->get(
    \Magento\Framework\Api\Data\VideoContentInterfaceFactory::class
);
$videoContent = $videoContentFactory->create();
$videoContent->setMediaType('external-video');
$videoContent->setVideoDescription('Video description');
$videoContent->setVideoProvider('youtube');
$videoContent->setVideoMetadata('Video Metadata');
$videoContent->setVideoTitle('Video title');
$videoContent->setVideoUrl('http://www.youtube.com/v/tH_2PFNmWoga');

$mediaGalleryEntryExtension->setVideoContent($videoContent);
$video->setExtensionAttributes($mediaGalleryEntryExtension);

/**
 * @var \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement
 */
$mediaGalleryManagement = $objectManager->get(
    \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface::class
);
$mediaGalleryManagement->create('configurable', $video);
