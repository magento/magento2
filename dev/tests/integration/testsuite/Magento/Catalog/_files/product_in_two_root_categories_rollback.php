<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

//$objectManager = Bootstrap::getObjectManager();

//$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
//$categoryCollectionFactory = $objectManager->get(CollectionFactory::class);

///** @var $categoryCollection Collection */
//$categoryCollection = $categoryCollectionFactory->create();
//$categoryCollection->addFieldToFilter('name', ['eq' => 'Second Root Category']);
//$secondRootCategory = $categoryCollection->getFirstItem();
//$categoryRepository->delete($secondRootCategory);

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store_with_second_root_category_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_category_rollback.php');
