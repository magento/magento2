<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\Data\VideoContentInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

include __DIR__ . '/product_simple_with_full_option_set.php';

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory */
$mediaGalleryEntryFactory = $objectManager->get(ProductAttributeMediaGalleryEntryInterfaceFactory::class);

/** @var ImageContentInterfaceFactory $imageContentFactory */
$imageContentFactory = $objectManager->get(ImageContentInterfaceFactory::class);
$imageContent = $imageContentFactory->create();
$testImagePath = __DIR__ . '/magento_image.jpg';
$imageContent->setBase64EncodedData(base64_encode(file_get_contents($testImagePath)));
$imageContent->setType("image/jpeg");
$imageContent->setName("1.jpg");

$video = $mediaGalleryEntryFactory->create();
$video->setDisabled(false);
$video->setFile('1.jpg');
$video->setLabel('Video Label');
$video->setMediaType('external-video');
$video->setPosition(2);
$video->setContent($imageContent);

/** @var ProductAttributeMediaGalleryEntryExtensionFactory $mediaGalleryEntryExtensionFactory */
$mediaGalleryEntryExtensionFactory = $objectManager->get(ProductAttributeMediaGalleryEntryExtensionFactory::class);
$mediaGalleryEntryExtension = $mediaGalleryEntryExtensionFactory->create();

/** @var VideoContentInterfaceFactory $videoContentFactory */
$videoContentFactory = $objectManager->get(VideoContentInterfaceFactory::class);
$videoContent = $videoContentFactory->create();
$videoContent->setMediaType('external-video');
$videoContent->setVideoDescription('Video description');
$videoContent->setVideoProvider('youtube');
$videoContent->setVideoMetadata('Video Metadata');
$videoContent->setVideoTitle('Video title');
$videoContent->setVideoUrl('http://www.youtube.com/v/tH_2PFNmWoga');

$mediaGalleryEntryExtension->setVideoContent($videoContent);
$video->setExtensionAttributes($mediaGalleryEntryExtension);

/** @var ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement */
$mediaGalleryManagement = $objectManager->get(ProductAttributeMediaGalleryManagementInterface::class);
$mediaGalleryManagement->create('simple', $video);
