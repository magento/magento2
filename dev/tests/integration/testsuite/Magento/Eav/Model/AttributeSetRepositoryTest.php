<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks the default sort order of the attribute set list.
 */
class AttributeSetRepositoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $repository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->get(AttributeSetRepositoryInterface::class);
    }

    /**
     * Without sort orders, pages are ordered by attribute_set_id and contain every attribute set once.
     *
     * @magentoDataFixture Magento/Eav/_files/attribute_set_for_search.php
     * @return void
     */
    public function testGetListWithoutSortOrderIsOrderedAcrossPages(): void
    {
        $total = $this->repository->getList($this->objectManager->create(SearchCriteriaBuilder::class)->create())
            ->getTotalCount();
        $this->assertGreaterThan(2, $total);

        $ids = [];
        $pages = (int)ceil($total / 2);
        for ($page = 1; $page <= $pages; $page++) {
            $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)
                ->setPageSize(2)
                ->setCurrentPage($page)
                ->create();
            foreach ($this->repository->getList($searchCriteria)->getItems() as $item) {
                $ids[] = (int)$item->getAttributeSetId();
            }
        }

        $sortedIds = $ids;
        sort($sortedIds);
        $this->assertSame($sortedIds, $ids, 'Items are not sorted by attribute_set_id ASC');
        $this->assertCount($total, array_unique($ids), 'Pages contain duplicated or missing items');
    }
}
