<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class AddressRepositoryTest
 */
class AddressRepositoryTest extends TestCase
{
    /** @var AddressRepository */
    protected $repository;

    /** @var  SortOrderBuilder */
    private $sortOrderBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->get(AddressRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $this->sortOrderBuilder = $this->objectManager->get(SortOrderBuilder::class);
    }

    /**
     * Test for get list with multiple filters and sorting
     *
     * @return void
     * @magentoDataFixture Magento/Sales/_files/address_list.php
     */
    public function testGetListWithMultipleFiltersAndSorting(): void
    {
        $filter1 = $this->filterBuilder
            ->setField('postcode')
            ->setConditionType('neq')
            ->setValue('ZX0789A')
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('address_type')
            ->setValue('billing')
            ->create();
        $filter3 = $this->filterBuilder
            ->setField('city')
            ->setValue('Ena4ka')
            ->create();
        $sortOrder = $this->sortOrderBuilder
            ->setField('region_id')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $this->searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var \Magento\Sales\Api\Data\OrderAddressSearchResultInterface $result */
        $result = $this->repository->getList($searchCriteria);
        $items = $result->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals('ZX0789', array_shift($items)->getPostcode());
        $this->assertEquals('47676', array_shift($items)->getPostcode());
    }

    /**
     * Test for formatting custom sales address multi-attribute
     *
     * @return void
     * @magentoDataFixture Magento/Sales/_files/order_address_with_multi_attribute.php
     */
    public function testFormatSalesAddressCustomMultiAttribute(): void
    {
        $address = $this->objectManager->get(OrderAddressInterface::class)
            ->load('multiattribute@example.com', 'email');
        $address->setData('fixture_address_multiselect_attribute', ['dog', 'cat']);
        $address->setData('fixture_address_multiline_attribute', ['dog', 'cat']);

        $this->objectManager->get(OrderAddressRepositoryInterface::class)
            ->save($address);
        $this->assertEquals('dog,cat', $address->getData('fixture_address_multiselect_attribute'));
        $this->assertEquals('dog'.PHP_EOL.'cat', $address->getData('fixture_address_multiline_attribute'));
    }
}
