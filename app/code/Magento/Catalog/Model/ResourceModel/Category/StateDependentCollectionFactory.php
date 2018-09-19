<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\ResourceModel\Category;

/**
 * Factory class for state dependent category collection
 */
class StateDependentCollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Catalog category flat state
     *
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    private $catalogCategoryFlatState;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $catalogCategoryFlatState
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $catalogCategoryFlatState
    ) {
        $this->objectManager = $objectManager;
        $this->catalogCategoryFlatState = $catalogCategoryFlatState;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(
            ($this->catalogCategoryFlatState->isAvailable()) ? Flat\Collection::class : Collection::class,
            $data
        );
    }
}
