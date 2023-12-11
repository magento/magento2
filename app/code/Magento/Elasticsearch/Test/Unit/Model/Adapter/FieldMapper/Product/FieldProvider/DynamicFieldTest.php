<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\DynamicField;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class DynamicFieldTest extends TestCase
{
    /**
     * @var DynamicField
     */
    private $provider;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var Collection
     */
    private $categoryCollection;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->groupRepository = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->indexTypeConverter = $this->getMockBuilder(IndexTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeAdapterProvider = $this->getMockBuilder(AttributeProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByAttributeCode'])
            ->getMock();
        $this->fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFieldName'])
            ->getMock();
        $this->categoryCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds'])
            ->getMock();
        $categoryCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $categoryCollection->method('create')
            ->willReturn($this->categoryCollection);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->provider = new DynamicField(
            $this->fieldTypeConverter,
            $this->indexTypeConverter,
            $this->groupRepository,
            $this->searchCriteriaBuilder,
            $this->fieldNameResolver,
            $this->attributeAdapterProvider,
            $this->categoryCollection,
            $this->storeManager,
            $categoryCollection
        );
    }

    /**
     * @param array $categoryIds
     * @param array $groupIds
     * @param array $context
     * @param array $expected
     * @dataProvider attributeProvider
     */
    public function testGetFields(
        array $categoryIds,
        array $groupIds,
        array $context,
        array $expected
    ): void {
        $websiteIdByStoreId = [
            1 => 1,
            2 => 1,
            3 => 2
        ];
        $this->storeManager->method('getStore')
            ->willReturnCallback(
                function ($storeId) use ($websiteIdByStoreId) {
                    return $this->createConfiguredMock(
                        StoreInterface::class,
                        [
                            'getId' => $storeId,
                            'getWebsiteId' => $websiteIdByStoreId[$storeId]
                        ]
                    );
                }
            );
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->method('create')
            ->willReturn($searchCriteria);
        $groupSearchResults = $this->getMockBuilder(GroupSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMockForAbstractClass();
        $groups = [];
        foreach ($groupIds as $groupId) {
            $groups[] = $this->createConfiguredMock(
                GroupInterface::class,
                [
                    'getId' => $groupId
                ]
            );
        }
        $groupSearchResults->method('getItems')
            ->willReturn($groups);

        $this->categoryCollection->method('getAllIds')
            ->willReturn($categoryIds);

        $this->fieldNameResolver->method('getFieldName')
            ->willReturnCallback(
                function ($attribute, $context) {
                    switch ($attribute->getAttributeCode()) {
                        case 'category_name':
                            return 'category_name_' . $context['categoryId'];
                        case 'position':
                            return 'position_' . $context['categoryId'];
                        case 'price':
                            return 'price_' . $context['customerGroupId'] . '_' . $context['websiteId'];
                        default:
                            return null;
                    }
                }
            );
        $this->indexTypeConverter->method('convert')
            ->willReturn('no_index');
        $this->groupRepository->method('getList')
            ->willReturn($groupSearchResults);
        $this->attributeAdapterProvider->method('getByAttributeCode')
            ->willReturnCallback(
                function ($code) {
                    return $this->createConfiguredMock(
                        AttributeAdapter::class,
                        [
                            'getAttributeCode' => $code
                        ]
                    );
                }
            );
        $this->fieldTypeConverter->method('convert')
            ->willReturnMap(
                [
                    ['string', 'string'],
                    ['float', 'double'],
                    ['integer', 'integer'],
                ]
            );

        $this->assertEquals(
            $expected,
            $this->provider->getFields($context)
        );
    }

    /**
     * @return array
     */
    public function attributeProvider(): array
    {
        return [
            [
                [1],
                [1],
                ['websiteId' => 1],
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'integer',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'double',
                        'store' => true
                    ]
                ]
            ],
            [
                [1],
                [1],
                ['websiteId' => 1],
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'integer',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'double',
                        'store' => true
                    ]
                ],
            ],
            [
                [1],
                [1],
                ['websiteId' => 1],
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'integer',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'double',
                        'store' => true
                    ]
                ]
            ],
            [
                [1],
                [1],
                [
                    'storeId' => 3,
                    'websiteId' => 3
                ],
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'integer',
                        'index' => 'no_index'
                    ],
                    'price_1_2' => [
                        'type' => 'double',
                        'store' => true
                    ]
                ]
            ]
        ];
    }
}
