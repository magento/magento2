<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Sets up common objects
     */
    protected function setUp()
    {
        $this->productRepository = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        $this->searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
    }

    /**
     * Checks filtering by store_id
     *
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     */
    public function testFilterByStoreId()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', '1', 'eq')
            ->create();
        $list = $this->productRepository->getList($searchCriteria);
        $count = $list->getTotalCount();

        $this->assertGreaterThanOrEqual(1, $count);
    }
}
