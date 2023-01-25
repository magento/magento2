<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_products.php');

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);
// Add frontend label to created attribute:
$frontendLabelAttribute = $objectManager->get(FrontendLabel::class);
$frontendLabelAttribute->setStoreId(1);
$frontendLabelAttribute->setLabel('Default Store View label');
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
$frontendLabels = $attribute->getFrontendLabels();
$frontendLabels[] = $frontendLabelAttribute;

$attribute->setFrontendLabels($frontendLabels);
$attributeRepository->save($attribute);
