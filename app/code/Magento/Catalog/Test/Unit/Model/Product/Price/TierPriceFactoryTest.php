<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterfaceFactory;
use Magento\Catalog\Model\Product\Price\TierPrice;
use Magento\Catalog\Model\Product\Price\TierPriceFactory;
use Magento\Catalog\Model\Product\Price\TierPricePersistence;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TierPriceFactoryTest extends TestCase
{
    /**
     * @var TierPriceInterfaceFactory|MockObject
     */
    private $tierPriceFactory;

    /**
     * @var TierPricePersistence|MockObject
     */
    private $tierPricePersistence;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $customerGroupRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var TierPriceFactory
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tierPriceFactory = $this->createMock(TierPriceInterfaceFactory::class);
        $this->tierPricePersistence = $this->createMock(TierPricePersistence::class);
        $this->customerGroupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->createMock(FilterBuilder::class);

        $this->model = new TierPriceFactory(
            $this->tierPriceFactory,
            $this->tierPricePersistence,
            $this->customerGroupRepository,
            $this->searchCriteriaBuilder,
            $this->filterBuilder
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param array $rawData
     * @param array $expected
     * @return void
     */
    public function testCreate(array $rawData, array $expected): void
    {
        $rawData = array_merge(
            [
                'value_id' => 1,
                'entity_id' => 1,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 2.0000,
                'value' => 2.0000,
                'percentage_value' => null,
                'website_id' => 0
            ],
            $rawData
        );
        $expected = array_merge(
            [
                'sku' => 'simple',
                'price' => 2.0,
                'price_type' => TierPrice::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'quantity' => 2.000,
                'customer_group' => 'all groups'
            ],
            $expected
        );
        $customerGroupMock = $this->getMockForAbstractClass(GroupInterface::class);
        $customerGroupMock->method('getCode')
            ->willReturn('NOT LOGGED IN');

        $isCustomerGroupResolved = isset($rawData['customer_group_code']) || $rawData['all_groups'];
        $this->customerGroupRepository->expects($isCustomerGroupResolved ? $this->never() : $this->once())
            ->method('getById')
            ->willReturn($customerGroupMock);
        $expectedTierPrice = $this->getMockBuilder(TierPrice::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $expectedTierPrice->setData($expected);
        $tierPriceMock = $this->getMockBuilder(TierPrice::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceFactory->method('create')
            ->willReturn($tierPriceMock);
        $tierPrice = $this->model->create($rawData, 'simple');
        $this->assertEquals($expectedTierPrice->getSku(), $tierPrice->getSku());
        $this->assertEquals($expectedTierPrice->getPrice(), $tierPrice->getPrice());
        $this->assertEquals($expectedTierPrice->getPriceType(), $tierPrice->getPriceType());
        $this->assertEquals($expectedTierPrice->getWebsiteId(), $tierPrice->getWebsiteId());
        $this->assertEquals($expectedTierPrice->getCustomerGroup(), $tierPrice->getCustomerGroup());
        $this->assertEquals($expectedTierPrice->getQuantity(), $tierPrice->getQuantity());
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            [
                [],
                []
            ],
            [
                [
                    'all_groups' => 0,
                    'customer_group_id' => 1,
                ],
                [
                    'customer_group' => 'NOT LOGGED IN'
                ]
            ],
            [
                [
                    'all_groups' => 0,
                    'customer_group_id' => 2,
                    'customer_group_code' => 'custom',
                ],
                [
                    'customer_group' => 'custom'
                ]
            ]
        ];
    }
}
