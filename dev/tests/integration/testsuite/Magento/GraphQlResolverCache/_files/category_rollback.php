<?php
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$collection = $objectManager->get(CollectionFactory::class)->create();
$collection->addAttributeToFilter('name', 'GraphQL Cache Category');
$repository = $objectManager->get(CategoryRepositoryInterface::class);
foreach ($collection as $category) {
    $repository->delete($category);
}
