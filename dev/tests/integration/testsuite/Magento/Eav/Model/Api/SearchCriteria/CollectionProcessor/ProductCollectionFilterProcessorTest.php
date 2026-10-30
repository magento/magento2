<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor as FrameworkFilterProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProductCollectionFilterProcessorTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testFrameworkFilterProcessorAppliesOrFiltersAcrossAttributes(): void
    {
        /** @var FrameworkFilterProcessor $filterProcessor */
        $filterProcessor = $this->objectManager->create(FrameworkFilterProcessor::class);

        $filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $filterGroupBuilder = $this->objectManager->get(FilterGroupBuilder::class);
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);

        $value = 'black';

        $filters = [
            $filterBuilder->setField('description')->setValue($value)->setConditionType('eq')->create(),
            $filterBuilder->setField('color')->setValue($value)->setConditionType('eq')->create(),
            $filterBuilder->setField('country_of_manufacture')->setValue($value)->setConditionType('eq')->create(),
        ];

        $filterGroup = $filterGroupBuilder->setFilters($filters)->create();
        $searchCriteria = $searchCriteriaBuilder->setFilterGroups([$filterGroup])->create();

        $collection = $this->objectManager->get(CollectionFactory::class)->create();
        $collection->addAttributeToSelect('*');

        $filterProcessor->process($searchCriteria, $collection);
        $collection->load();

        $this->assertSame(0, $collection->getSize());
    }
}
