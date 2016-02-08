<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$product = $objectManager->create('Magento\Catalog\Model\Product');
$sampleFactory = $objectManager->create('Magento\Downloadable\Api\Data\SampleInterfaceFactory');
$linkFactory = $objectManager->create('Magento\Downloadable\Api\Data\LinkInterfaceFactory');

$downloadableData = [
    'link' => [
        [
            'link_id' => 0,
            'product_id' => 1,
            'sort_order' => '0',
            'title' => 'Downloadable Product Link',
            'sample' => [
                'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
                'url' => null,
                'file' => json_encode(
                    [
                        [
                            'file' => '/n/d/jellyfish_1_3.jpg',
                            'name' => 'jellyfish_1_3.jpg',
                            'size' => 54565,
                            'status' => 0,
                        ],
                    ]
                ),
            ],
            'file' => json_encode(
                [
                    [
                        'file' => '/j/e/jellyfish_2_4.jpg',
                        'name' => 'jellyfish_2_4.jpg',
                        'size' => 56644,
                        'status' => 0,
                    ],
                ]
            ),
            'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
            'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
            'link_url' => null,
            'is_delete' => 0,
            'number_of_downloads' => 15,
            'price' => 15.00,
        ],
    ],
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
)->setDownloadableData($downloadableData);

$extension = $product->getExtensionAttributes();
$links = [];
foreach ($downloadableData['link'] as $linkData) {
    if (!$linkData || (isset($linkData['is_delete']) && (bool)$linkData['is_delete'])) {
        continue;
    } else {
        unset($linkData['link_id']);
        // TODO: need to implement setLinkFileContent()
        $link = $linkFactory->create(['data' => $linkData]);
        $link->setId(null);
        $link->setSampleType($linkData['sample']['type']);
        $link->setSampleFileData($linkData['sample']['file']);
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
    }
}

$extension->setDownloadableProductLinks($links);

if (isset($downloadableData['sample']) && is_array($downloadableData['sample'])) {
    $samples = [];
    foreach ($downloadableData['sample'] as $sampleData) {
        if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
            continue;
        } else {
            unset($sampleData['sample_id']);
            $sample = $sampleFactory->create(['data' => $sampleData]);
            $sample->setId(null);
            $sample->setStoreId($product->getStoreId());
            $sample->setSampleType($sampleData['type']);
            $sample->setSampleUrl($sampleData['sample_url']);
            if (!$sample->getSortOrder()) {
                $sample->setSortOrder(1);
            }
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
