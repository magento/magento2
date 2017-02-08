<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require dirname(dirname(__DIR__)) . '/Store/_files/website.php';
require 'create_products.php';

/** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
$groupPriceAttribute = $attributeRepository->get('tier_price')
    ->setScope(Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE);
$attributeRepository->save($groupPriceAttribute);

$productModel->setWebsiteIds(array_merge($productModel->getWebsiteIds(), [(int)$website->getId()]));
$productModel->setTierPrice(
    [
        [
            'website_id' => $website->getId(),
            'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
            'price_qty'  => 3,
            'price'      => 5
        ]
    ]
);
$productModel->save();
