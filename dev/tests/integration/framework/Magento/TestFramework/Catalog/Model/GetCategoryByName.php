<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Load category by category name
 */
class GetCategoryByName
{
    /** @var CollectionFactory */
    private $categoryCollectionFactory;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(CollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Load category by name.
     *
     * @param string $categoryName
     * @return CategoryInterface
     */
    public function execute(string $categoryName): CategoryInterface
    {
        $categoryCollection = $this->categoryCollectionFactory->create();

        return $categoryCollection->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getFirstItem();
    }
}
