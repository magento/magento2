<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;

/**
 * @SuppressWarnings(PHPMD)
 */
class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolver;

    /**
     * @var AdvancedPricing\Validator\TierPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tierPrice;

    protected function setUp()
    {
        $this->groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->storeResolver = $this->createMock(
            \Magento\CatalogImportExport\Model\Import\Product\StoreResolver::class
        );

        $this->tierPrice = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice::class
        )
            ->setMethods(['isValidValueAndLength', 'hasEmptyColumns', '_addMessages'])
            ->setConstructorArgs([$this->groupRepository, $this->searchCriteriaBuilder, $this->storeResolver])
            ->getMock();
    }

    public function testInitInternalCalls()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->expects($this->any())->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->expects($this->any())->method('getItems')->willReturn($groups);

        $this->tierPrice->init(null);
    }

    public function testInitAddToCustomerGroups()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
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
        $priceContextMock = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Product::class)
            ->setConstructorArgs(
                [
                    \Magento\Framework\Json\Helper\Data::class,
                    \Magento\ImportExport\Helper\Data::class,
                    \Magento\ImportExport\Model\ResourceModel\Import\Data::class,
                    \Magento\Eav\Model\Config::class,
                    \Magento\Framework\App\ResourceConnection::class,
                    \Magento\ImportExport\Model\ResourceModel\Helper::class,
                    \Magento\Framework\Stdlib\StringUtils::class,
                    'ProcessingErrorAggregatorInterface'
                ]
            );

        $this->tierPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->tierPrice->expects($this->any())->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->setPropertyValue($this->tierPrice, 'customerGroups', $customerGroups);

        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->groupRepository
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->expects($this->any())->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->expects($this->any())->method('getItems')->willReturn($groups);

        $this->tierPrice->init($priceContextMock);
        $this->tierPrice->isValid($value);
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
