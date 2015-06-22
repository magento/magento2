<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing\Validator;

use \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as AdvancedPricing;

/**
 * @SuppressWarnings(PHPMD)
 */
class GroupPriceTest extends \PHPUnit_Framework_TestCase
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
     * @var  AdvancedPricing\Validator\GroupPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupPrice;

    public function setUp()
    {
        $this->groupRepository = $this->getMockBuilder('\Magento\Customer\Api\GroupRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->storeResolver = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\StoreResolver',
            [],
            [],
            '',
            false
        );

        $this->groupPrice = $this->getMock(
            'Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\GroupPrice',
            ['isValidValueAndLength', 'hasEmptyColumns', '_addMessages'],
            [
                $this->groupRepository,
                $this->searchCriteriaBuilder,
                $this->storeResolver,
            ],
            ''
        );
    }

    public function testInitInternalCalls()
    {
        $searchCriteria = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            '\Magento\Customer\Api\Data\GroupSearchResultsInterface',
            [],
            '',
            false
        );
        $this->groupRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder('\Magento\Customer\Api\Data\GroupInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->once())->method('getCode');
        $groupTest->expects($this->once())->method('getId');
        $groups = [$groupTest];
        $groupSearchResult->expects($this->once())->method('getItems')->willReturn($groups);

        $this->groupPrice->init();
    }

    public function testInitAddToCustomerGroups()
    {
        $searchCriteria = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $groupSearchResult = $this->getMockForAbstractClass(
            '\Magento\Customer\Api\Data\GroupSearchResultsInterface',
            [],
            '',
            false
        );
        $this->groupRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($groupSearchResult);

        $groupTest = $this->getMockBuilder('\Magento\Customer\Api\Data\GroupInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();

        $expectedCode = 'code';
        $expectedId = 'id';
        $expectedCustomerGroups = [
            $expectedCode => $expectedId,
        ];
        $groupTest->expects($this->once())->method('getCode')->willReturn($expectedCode);
        $groupTest->expects($this->once())->method('getId')->willReturn($expectedId);
        $groups = [$groupTest];
        $groupSearchResult->expects($this->once())->method('getItems')->willReturn($groups);

        $this->groupPrice->init();
        $this->assertEquals($expectedCustomerGroups, $this->getPropertyValue($this->groupPrice, 'customerGroups'));
    }

    public function testIsValidInitCall()
    {
        $groupPrice = $this->groupPrice = $this->getMock(
            'Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\GroupPrice',
            ['init', '_clearMessages'],
            [
                $this->groupRepository,
                $this->searchCriteriaBuilder,
                $this->storeResolver,
            ],
            ''
        );
        $groupPrice->expects($this->once())->method('_clearMessages');
        $this->setPropertyValue($groupPrice, 'customerGroups', false);
        $groupPrice->expects($this->once())->method('init');

        $groupPrice->isValid([]);
    }

    /**
     * @dataProvider isValidResultFalseDataProvider
     *
     * @param array $value
     * @param array $hasEmptyColumns
     * @param array $customerGroups
     */
    public function testIsValidResultFalse($value, $hasEmptyColumns, $customerGroups)
    {
        $this->groupPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->groupPrice->expects($this->any())->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->setPropertyValue($this->groupPrice, 'customerGroups', $customerGroups);

        $result = $this->groupPrice->isValid($value);
        $this->assertFalse($result);
    }

    public function testIsValidResultTrue()
    {
        $this->groupPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(false);
        $this->setPropertyValue($this->groupPrice, 'customerGroups', true);

        $result = $this->groupPrice->isValid([]);
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
        $this->groupPrice->expects($this->once())->method('isValidValueAndLength')->willReturn(true);
        $this->groupPrice->expects($this->any())->method('hasEmptyColumns')->willReturn($hasEmptyColumns);
        $this->setPropertyValue($this->groupPrice, 'customerGroups', $customerGroups);

        $this->groupPrice->expects($this->once())->method('_addMessages')->with($expectedMessages);
        $this->groupPrice->isValid($value);
    }

    public function testGetCustomerGroupsInitCall()
    {
        $groupPrice = $this->groupPrice = $this->getMock(
            'Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\GroupPrice',
            ['init'],
            [
                $this->groupRepository,
                $this->searchCriteriaBuilder,
                $this->storeResolver,
            ],
            ''
        );
        $this->setPropertyValue($groupPrice, 'customerGroups', false);
        $groupPrice->expects($this->once())->method('init');

        $groupPrice->getCustomerGroups();
    }

    public function isValidResultFalseDataProvider()
    {
        return [
            // First if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'value',
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => null,
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'value',
                ],
                '$hasEmptyColumns' => true,
                '$customerGroups' => [
                    'value' => 'value'
                ],
            ],
            // Second if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => AdvancedPricing::VALUE_ALL_GROUPS,
                ],
                '$hasEmptyColumns' => false,
                '$customerGroups' => [
                    'group price customer value' => 'value'
                ],
            ],
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'group price customer value',
                ],
                '$hasEmptyColumns' => false,
                '$customerGroups' => [
                    'group price customer value' => null
                ],
            ],
        ];
    }

    public function isValidAddMessagesCallDataProvider()
    {
        return [
            // First if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => null,
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::VALUE_ALL_GROUPS => 'value',
                ],
                '$hasEmptyColumns' => null,
                '$customerGroups' => [
                    'value' => 'value'
                ],
                '$expectedMessages' => [AdvancedPricing\Validator::ERROR_GROUP_PRICE_DATA_INCOMPLETE],
            ],
            // Second if condition cases.
            [
                '$value' => [
                    AdvancedPricing::COL_GROUP_PRICE_WEBSITE => 'value',
                    AdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => 'value',
                    AdvancedPricing::VALUE_ALL_GROUPS => 'not ALL GROUPS',
                ],
                '$hasEmptyColumns' => false,
                '$customerGroups' => [
                    'value' => null
                ],
                '$expectedMessages' => [AdvancedPricing\Validator::ERROR_INVALID_GROUP_PRICE_GROUP],
            ],
        ];
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
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
     * @param $object
     * @param $property
     * @param $value
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
