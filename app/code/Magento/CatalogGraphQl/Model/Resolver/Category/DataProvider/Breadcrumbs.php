<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Breadcrumbs data provider
 */
class Breadcrumbs
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Uid $uidEncoder = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
    }

    /**
     * Get breadcrumbs data
     *
     * @param string $categoryPath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(string $categoryPath): array
    {
        $breadcrumbsData = [];

        $pathCategoryIds = explode('/', $categoryPath);
        $parentCategoryIds = array_slice($pathCategoryIds, 2, -1);

        if (count($parentCategoryIds)) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect(['name', 'url_key', 'url_path']);
            $collection->addAttributeToFilter('entity_id', $parentCategoryIds);
            $collection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, 1);

            foreach ($collection as $category) {
                $breadcrumbsData[] = [
                    'category_id' => $category->getId(),
                    'category_uid' => $this->uidEncoder->encode((string) $category->getId()),
                    'category_name' => $category->getName(),
                    'category_level' => $category->getLevel(),
                    'category_url_key' => $category->getUrlKey(),
                    'category_url_path' => $category->getUrlPath(),
                ];
            }
        }
        return $breadcrumbsData;
    }
}
