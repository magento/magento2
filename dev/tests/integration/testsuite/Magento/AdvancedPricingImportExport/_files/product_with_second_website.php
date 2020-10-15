<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Store\Api\WebsiteRepositoryInterface;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website.php');
Resolver::getInstance()->requireDataFixture('Magento/AdvancedPricingImportExport/_files/create_products.php');

$objectManager = Bootstrap::getObjectManager();

/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$groupPriceAttribute = $attributeRepository->get('tier_price')->setScope(ScopedAttributeInterface::SCOPE_WEBSITE);
$attributeRepository->save($groupPriceAttribute);

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$productModel = $productRepository->get('AdvancedPricingSimple 2');
$productModel->setWebsiteIds(array_merge($productModel->getWebsiteIds(), [(int)$website->getId()]));
$productModel->setTierPrice(
    [
        [
            'website_id' => $website->getId(),
            'cust_group' => Group::CUST_GROUP_ALL,
            'price_qty'  => 3,
            'price'      => 5
        ]
    ]
);
$productRepository->save($productModel);
