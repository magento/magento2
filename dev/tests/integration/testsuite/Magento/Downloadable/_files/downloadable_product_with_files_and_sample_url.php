<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Downloadable\Api\DomainManagerInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var DomainManagerInterface $domainManager */
$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->addDomains(['example.com', 'sampleurl.com']);

/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Downloadable Product')
    ->setSku('downloadable-product')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setLinksPurchasedSeparately(true)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    )->setLinksTitle(
        'Downloadable Product Link Title'
    );

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory
 */
$linkFactory = $objectManager->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$links = [];
$linkData = [
    'title' => 'Downloadable Product Link',
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => 'http://example.com/downloadable.txt',
    'link_id' => 0,
    'is_delete' => null,
];
$link = $linkFactory->create(['data' => $linkData]);
$link->setId(null);
$link->setLinkType($linkData['type']);
$link->setStoreId($product->getStoreId());
$link->setWebsiteId($product->getStore()->getWebsiteId());
$link->setProductWebsiteIds($product->getWebsiteIds());
$link->setSortOrder(1);
$link->setPrice(0);
$link->setNumberOfDownloads(0);
$links[] = $link;

$linkData = [
    'title' => 'Downloadable Product Link',
    'sample' => [
        'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_URL,
        'url' => 'http://sampleUrl.com',
    ],
    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
    'link_url' => 'http://example.com/downloadable.txt',
    'is_delete' => null,
    'number_of_downloads' => 0,
    'price' => 0,
];
/**
 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $sampleContent
 */
$sampleContent = $objectManager->create(\Magento\Downloadable\Api\Data\File\ContentInterfaceFactory::class)->create();
$sampleContent->setFileData(
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    base64_encode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'test_image.jpg'))
);
$sampleContent->setName('jellyfish_1_3.jpg');
$sampleLink = $linkFactory->create(['data' => $linkData]);
$sampleLink->setId(null);
$sampleLink->setSampleType($linkData['sample']['type']);
$sampleLink->setSampleFileContent($sampleContent);
$sampleLink->setSampleUrl($linkData['sample']['url']);
$sampleLink->setLinkType($linkData['type']);
$sampleLink->setStoreId($product->getStoreId());
$sampleLink->setWebsiteId($product->getStore()->getWebsiteId());
$sampleLink->setProductWebsiteIds($product->getWebsiteIds());
$sampleLink->setSortOrder(2);
/**
 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $content
 */
$content = $objectManager->create(\Magento\Downloadable\Api\Data\File\ContentInterfaceFactory::class)->create();
$content->setFileData(
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    base64_encode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'test_image.jpg'))
);
$content->setName('jellyfish_2_4.jpg');
$sampleLink->setLinkFileContent($content);
$links[] = $sampleLink;

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

$extension = $product->getExtensionAttributes();

$samples = [];
$sampleFactory = $objectManager->create(\Magento\Downloadable\Api\Data\SampleInterfaceFactory::class);
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
        $content = $objectManager->create(
            \Magento\Downloadable\Api\Data\File\ContentInterfaceFactory::class
        )->create();
        $content->setFileData(
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            base64_encode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'test_image.jpg'))
        );
        $content->setName('jellyfish_1_4.jpg');
        $sample->setSampleFileContent($content);
        $sample->setSortOrder($sampleData['sort_order']);
        $samples[] = $sample;
    }
}

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductSamples($samples);
$extension->setDownloadableProductLinks($links);
$product->setExtensionAttributes($extension);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product)->getData();
