<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AttributeSetRepositoryTest extends TestCase
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $repository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->repository = $objectManager->get(AttributeSetRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_set_for_search.php
     */
    public function testGetListWithoutSortOrder()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(20)
            ->setCurrentPage(1)
            ->create();

        $searchResult = $this->repository->getList($searchCriteria);
        
        $this->assertGreaterThan(1, $searchResult->getTotalCount());

        $items = array_values($searchResult->getItems());
        for ($i = 1; $i < count($items); $i++) {
            $this->assertGreaterThan(
                $items[$i - 1]->getAttributeSetId(),
                $items[$i]->getAttributeSetId(),
                'Items are not sorted by attribute_set_id ASC'
            );
        }
    }
}
