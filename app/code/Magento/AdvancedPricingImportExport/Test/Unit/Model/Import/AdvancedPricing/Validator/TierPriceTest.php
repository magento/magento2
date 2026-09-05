<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Helper\Data as ImportExportHelperData;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ResourceImportData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD)
 */
class TierPriceTest extends TestCase
{
    /**
     * @var GroupRepositoryInterface|MockObject
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var StoreResolver|MockObject
     */
    protected $storeResolver;

    /**
     * @var AdvancedPricing\Validator\TierPrice|MockObject
     */
    protected $tierPrice;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);

        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->storeResolver = $this->createMock(
            StoreResolver::class
        );

        $this->tierPrice = $this->getMockBuilder(
            TierPrice::class
        )
            ->onlyMethods(['isValidValueAndLength', 'hasEmptyColumns', '_addMessages'])
            ->setConstructorArgs([$this->groupRepository, $this->searchCriteriaBuilder, $this->storeResolver])
            ->getMock();
    }

    public function testInitInternalCalls()
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->createMock(GroupSearchResultsInterface::class);
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->createMock(GroupInterface::class);
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->method('getItems')->willReturn($groups);

        $productMock = $this->createMock(Product::class);
        $this->tierPrice->init($productMock);
    }

    public function testInitAddToCustomerGroups()
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->createMock(GroupSearchResultsInterface::class);
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->createMock(GroupInterface::class);

        $expectedCode = 'code';
        $expectedId = 'id';
        $expectedCustomerGroups = [
            $expectedCode => $expectedId,
        ];
        $groupTest->expects($this->once())->method('getCode')->willReturn($expectedCode);
        $groupTest->method('getId')->willReturn($expectedId);
        $groups = [$groupTest];
        $groupSearchResult->method('getItems')->willReturn($groups);

        $productMock = $this->createMock(Product::class);
        $this->tierPrice->init($productMock);

        $reflection = new ReflectionClass($this->tierPrice);
        $property = $reflection->getProperty('customerGroups');
        $this->assertEquals($expectedCustomerGroups, $property->getValue($this->tierPrice));
    }

    public function testIsValidResultTrue()
    {
        $this->tierPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(false);
        $this->objectManager->setBackwardCompatibleProperty($this->tierPrice, 'customerGroups', true);

        $result = $this->tierPrice->isValid([]);
        $this->assertTrue($result);
    }

    /**
     *
     * @param array $value
     * @param bool  $hasEmptyColumns
     * @param array $customerGroups
     * @param array $expectedMessages
     */
    #[DataProvider('isValidAddMessagesCallDataProvider')]
    public function testIsValidAddMessagesCall($value, $hasEmptyColumns, $customerGroups, $expectedMessages)
    {
        $priceContextMock = $this->createMock(Product::class);

        $this->tierPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->tierPrice->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->objectManager->setBackwardCompatibleProperty($this->tierPrice, 'customerGroups', $customerGroups);

        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->createMock(GroupSearchResultsInterface::class);
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->createMock(GroupInterface::class);
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->method('getItems')->willReturn($groups);

        $this->tierPrice->init($priceContextMock);
        $this->tierPrice->isValid($value);
    }

    /**
     * @return array
     */
    public static function isValidResultFalseDataProvider()
    {
        return [
            // First if condition cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => null,
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => null,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => null,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => true,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            // Second if condition  cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'not ALL GROUPS',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            // Third if condition cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => -1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => -1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function isValidAddMessagesCallDataProvider()
    {
        return [
            // First if condition cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
                'expectedMessages' => [Validator::ERROR_TIER_DATA_INCOMPLETE],
            ],
            // Second if condition cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'not ALL GROUPS',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
                'expectedMessages' => [Validator::ERROR_INVALID_TIER_PRICE_GROUP],
            ],
            // Third if condition cases.
            [
                'value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => -1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                'hasEmptyColumns' => null,
                'customerGroups' => [
                    'value' => 'value'
                ],
                'expectedMessages' => [Validator::ERROR_INVALID_TIER_PRICE_QTY],
            ],
        ];
    }
}
