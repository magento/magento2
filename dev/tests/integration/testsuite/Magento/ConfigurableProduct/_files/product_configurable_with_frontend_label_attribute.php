<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_products.php');

// Add frontend label to created attribute:
$frontendLabelAttribute = Bootstrap::getObjectManager()->get(FrontendLabel::class);
$frontendLabelAttribute->setStoreId(1);
$frontendLabelAttribute->setLabel('Default Store View label');

$frontendLabels = $attribute->getFrontendLabels();
$frontendLabels[] = $frontendLabelAttribute;

$attribute->setFrontendLabels($frontendLabels);
$attributeRepository->save($attribute);
