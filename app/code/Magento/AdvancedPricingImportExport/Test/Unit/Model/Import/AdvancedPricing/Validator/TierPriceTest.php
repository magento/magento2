<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
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
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Helper\Data as ImportExportHelper;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceModelHelper;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TierPriceTest extends TestCase
{
    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolverMock;

    /**
     * @var TierPrice|MockObject
     */
    private $tierPriceMock;

    protected function setUp(): void
    {
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->storeResolverMock = $this->createMock(
            StoreResolver::class
        );

        $this->tierPriceMock = $this->getMockBuilder(TierPrice::class)
            ->setMethods(['isValidValueAndLength', 'hasEmptyColumns', '_addMessages'])
            ->setConstructorArgs([
                $this->groupRepositoryMock,
                $this->searchCriteriaBuilderMock,
                $this->storeResolverMock
            ])
            ->getMock();
    }

    public function testInitInternalCalls()
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(GroupSearchResultsInterface::class, [], '', false);
        $this->groupRepositoryMock
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->expects($this->any())->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->expects($this->any())->method('getItems')->willReturn($groups);

        $this->tierPriceMock->init(null);
    }

    public function testInitAddToCustomerGroups()
    {
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(GroupSearchResultsInterface::class, [], '', false);
        $this->groupRepositoryMock
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();

        $expectedCode = 'code';
        $expectedId = 'id';
        $expectedCustomerGroups = [
            $expectedCode => $expectedId,
        ];
        $groupTest->expects($this->once())->method('getCode')->willReturn($expectedCode);
        $groupTest->expects($this->any())->method('getId')->willReturn($expectedId);
        $groups = [$groupTest];
        $groupSearchResult->expects($this->any())->method('getItems')->willReturn($groups);

        $this->tierPriceMock->init(null);

        $this->assertEquals($expectedCustomerGroups, $this->getPropertyValue($this->tierPriceMock, 'customerGroups'));
    }

    public function testIsValidResultTrue()
    {
        $this->tierPriceMock->expects($this->once())->method('isValidValueAndLength')->willReturn(false);
        $this->setPropertyValue($this->tierPriceMock, 'customerGroups', true);

        $result = $this->tierPriceMock->isValid([]);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider isValidAddMessagesCallDataProvider
     *
     * @param array $value
     * @param bool $hasEmptyColumns
     * @param array $customerGroups
     * @param array $expectedMessages
     */
    public function testIsValidAddMessagesCall($value, $hasEmptyColumns, $customerGroups)
    {
        $priceContextMock = $this->getMockBuilder(Product::class)
            ->setConstructorArgs(
                [
                    JsonHelper::class,
                    ImportExportHelper::class,
                    ImportHelper::class,
                    Config::class,
                    ResourceConnection::class,
                    ResourceModelHelper::class,
                    StringUtils::class,
                    'ProcessingErrorAggregatorInterface'
                ]
            );

        $this->tierPriceMock->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->tierPriceMock->expects($this->any())->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->setPropertyValue($this->tierPriceMock, 'customerGroups', $customerGroups);

        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(GroupSearchResultsInterface::class, [], '', false);
        $this->groupRepositoryMock
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->expects($this->any())->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->expects($this->any())->method('getItems')->willReturn($groups);

        $this->tierPriceMock->init($priceContextMock);
        $this->tierPriceMock->isValid($value);
    }

    /**
     * @return array
     */
    public function isValidResultFalseDataProvider()
    {
        return [
            // First if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => null,
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => null,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => null,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => true,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            // Second if condition  cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'not ALL GROUPS',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            // Third if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => -1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => -1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function isValidAddMessagesCallDataProvider()
    {
        return [
            // First if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
                '$expectedMessages' => [AdvancedPricing\Validator::ERROR_TIER_DATA_INCOMPLETE],
            ],
            // Second if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'not ALL GROUPS',
                    AdvancedPricing::COL_TIER_PRICE_QTY => 1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
                '$expectedMessages' => [AdvancedPricing\Validator::ERROR_INVALID_TIER_PRICE_GROUP],
            ],
            // Third if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_TIER_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::COL_TIER_PRICE_QTY => -1000,
                    AdvancedPricing::COL_TIER_PRICE => 1000,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
                '$expectedMessages' => [AdvancedPricing\Validator::ERROR_INVALID_TIER_PRICE_QTY],
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
