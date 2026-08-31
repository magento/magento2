<?php
declare(strict_types=1);

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$collection = $objectManager->get(CollectionFactory::class)->create();
$collection->addFieldToFilter('identifier', 'graphql_cache_page');
$repository = $objectManager->get(PageRepositoryInterface::class);
foreach ($collection as $page) {
    $repository->delete($page);
}
