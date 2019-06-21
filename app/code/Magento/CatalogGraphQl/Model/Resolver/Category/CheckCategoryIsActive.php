<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Check if category is active.
 */
class CheckCategoryIsActive
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @param CollectionFactory $collectionFactory
     * @param MetadataPool $metadata
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        MetadataPool $metadata
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->metadata = $metadata;
    }

    /**
     * Check if category is active.
     *
     * @param int $rootCategoryId
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(int $rootCategoryId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter(Category::KEY_IS_ACTIVE, ['eq' => 1])
            ->getSelect()
            ->where(
                $collection->getSelect()
                    ->getConnection()
                    ->quoteIdentifier(
                        'e.' .
                        $this->metadata->getMetadata(CategoryInterface::class)->getIdentifierField()
                    ) . ' = ?',
                $rootCategoryId
            );

        if ($collection->count() === 0) {
            throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
        }
    }
}
