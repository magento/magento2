<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

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
use Magento\ImportExport\Model\ResourceModel\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp(): void
    {
        $this->groupRepository = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();

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
        $groupSearchResult = $this->getMockForAbstractClass(
            GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->method('getItems')->willReturn($groups);

        $this->tierPrice->init(null);
    }

    public function testInitAddToCustomerGroups()
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();

        $expectedCode = 'code';
        $expectedId = 'id';
        $expectedCustomerGroups = [
            $expectedCode => $expectedId,
        ];
        $groupTest->expects($this->once())->method('getCode')->willReturn($expectedCode);
        $groupTest->method('getId')->willReturn($expectedId);
        $groups = [$groupTest];
        $groupSearchResult->method('getItems')->willReturn($groups);

        $this->tierPrice->init(null);

        $this->assertEquals($expectedCustomerGroups, $this->getPropertyValue($this->tierPrice, 'customerGroups'));
    }

    public function testIsValidResultTrue()
    {
        $this->tierPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(false);
        $this->setPropertyValue($this->tierPrice, 'customerGroups', true);

        $result = $this->tierPrice->isValid([]);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider isValidAddMessagesCallDataProvider
     *
     * @param array $value
     * @param bool  $hasEmptyColumns
     * @param array $customerGroups
     * @param array $expectedMessages
     */
    public function testIsValidAddMessagesCall($value, $hasEmptyColumns, $customerGroups, $expectedMessages)
    {
        $priceContextMock = $this->getMockBuilder(Product::class)
            ->setConstructorArgs(
                [
                    Data::class,
                    \Magento\ImportExport\Helper\Data::class,
                    \Magento\ImportExport\Model\ResourceModel\Import\Data::class,
                    Config::class,
                    ResourceConnection::class,
                    Helper::class,
                    StringUtils::class,
                    'ProcessingErrorAggregatorInterface'
                ]
            );

        $this->tierPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->tierPrice->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->setPropertyValue($this->tierPrice, 'customerGroups', $customerGroups);

        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
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

    /**
     * Get any object property value.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getPropertyValue($object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Set object property value.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @return object
     * @throws \ReflectionException
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }
}
