<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$sampleFactory = $objectManager->create('Magento\Downloadable\Api\Data\SampleInterfaceFactory');
$linkFactory = $objectManager->create('Magento\Downloadable\Api\Data\LinkInterfaceFactory');

$downloadableData = [
    'sample' => [
        [
            'is_delete' => 0,
            'sample_id' => 0,
            'title' => 'Downloadable Product Sample Title',
            'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
            'file' => json_encode(
                [
                    [
                        'file' => '/f/u/jellyfish_1_4.jpg',
                        'name' => 'jellyfish_1_4.jpg',
                        'size' => 1024,
                        'status' => 0,
                    ],
                ]
            ),
            'sample_url' => null,
            'sort_order' => '0',
        ],
    ],
];
$product->setTypeId(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Downloadable Product'
)->setSku(
    'downloadable-product'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
);

$extension = $product->getExtensionAttributes();
$links = [];
$linkData = [
    'product_id' => 1,
    'sort_order' => '0',
    'title' => 'Downloadable Product Link',
    'sample' => [
        'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
        'url' => null,
    ],
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => null,
    'is_delete' => 0,
    'number_of_downloads' => 15,
    'price' => 15.00,
];
$link = $linkFactory->create(['data' => $linkData]);
$link->setId(null);
$link->setSampleType($linkData['sample']['type']);

/**
 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $content
 */
$content = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
$content->setFileData(
    base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.'test_image.jpg'))
);
$content->setName('jellyfish_2_4.jpg');
//$content->setName('');
$link->setLinkFileContent($content);

/**
 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $sampleContent
 */
$sampleContent = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
$sampleContent->setFileData(
    base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.'test_image.jpg'))
);
$sampleContent->setName('jellyfish_1_3.jpg');
$link->setSampleFileContent($sampleContent);
$link->setSampleUrl($linkData['sample']['url']);
$link->setLinkType($linkData['type']);
$link->setStoreId($product->getStoreId());
$link->setWebsiteId($product->getStore()->getWebsiteId());
$link->setProductWebsiteIds($product->getWebsiteIds());
if (!$link->getSortOrder()) {
    $link->setSortOrder(1);
}
if (null === $link->getPrice()) {
    $link->setPrice(0);
}
if ($link->getIsUnlimited()) {
    $link->setNumberOfDownloads(0);
}
$links[] = $link;



$extension->setDownloadableProductLinks($links);

if (isset($downloadableData['sample']) && is_array($downloadableData['sample'])) {
    $samples = [];
    foreach ($downloadableData['sample'] as $sampleData) {
        if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
            continue;
        } else {
            unset($sampleData['sample_id']);
            /**
             * @var \Magento\Downloadable\Api\Data\SampleInterface $sample
             */
            $sample = $sampleFactory->create(['data' => $sampleData]);
            $sample->setId(null);
            $sample->setStoreId($product->getStoreId());
            $sample->setSampleType($sampleData['type']);
            $sample->setSampleUrl($sampleData['sample_url']);
            /**
             * @var \Magento\Downloadable\Api\Data\File\ContentInterface $content
             */
            $content = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
            $content->setFileData(
                base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.'test_image.jpg'))
            );
            $content->setName('jellyfish_1_4.jpg');
            $sample->setSampleFileContent($content);
            $sample->setSortOrder($sampleData['sort_order']);
            $samples[] = $sample;
        }
    }
    $extension->setDownloadableProductSamples($samples);
}
$product->setExtensionAttributes($extension);

if ($product->getLinksPurchasedSeparately()) {
    $product->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
} else {
    $product->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
}
$product->save();
