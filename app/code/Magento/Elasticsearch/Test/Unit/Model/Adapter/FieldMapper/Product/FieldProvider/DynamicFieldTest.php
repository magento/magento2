<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Catalog\Model\ResourceModel\Category\Collection;

/**
 * @SuppressWarnings(PHPMD)
 */
class DynamicFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\DynamicField
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
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * @var Collection
     */
    private $categoryCollection;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->groupRepository = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexTypeConverter = $this->getMockBuilder(IndexTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeAdapterProvider = $this->getMockBuilder(AttributeProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByAttributeCode', 'getByAttribute'])
            ->getMock();
        $this->fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldName'])
            ->getMock();
        $this->categoryList = $this->getMockBuilder(CategoryListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->provider = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\DynamicField::class,
            [
                'groupRepository' => $this->groupRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'fieldTypeConverter' => $this->fieldTypeConverter,
                'indexTypeConverter' => $this->indexTypeConverter,
                'attributeAdapterProvider' => $this->attributeAdapterProvider,
                'categoryList' => $this->categoryList,
                'fieldNameResolver' => $this->fieldNameResolver,
                'categoryCollection' => $this->categoryCollection,
            ]
        );
    }

    /**
     * @dataProvider attributeProvider
     * @param $complexType
     * @param $categoryId
     * @param $groupId
     * @param array $expected
     * @return void
     */
    public function testGetAllAttributesTypes(
        $complexType,
        $categoryId,
        $groupId,
        $expected
    ) {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteria);
        $groupSearchResults = $this->getMockBuilder(GroupSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $group = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $group->expects($this->any())
            ->method('getId')
            ->willReturn($groupId);
        $groupSearchResults->expects($this->any())
            ->method('getItems')
            ->willReturn([$group]);

        $this->categoryCollection->expects($this->any())
            ->method('getAllIds')
            ->willReturn([$categoryId]);

        $categoryAttributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $categoryAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('category');
        $positionAttributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $positionAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('position');

        $this->fieldNameResolver->expects($this->any())
            ->method('getFieldName')
            ->will($this->returnCallback(
                function ($attribute) use ($categoryId) {
                    static $callCount = [];
                    $attributeCode = $attribute->getAttributeCode();
                    $callCount[$attributeCode] = !isset($callCount[$attributeCode]) ? 1 : ++$callCount[$attributeCode];

                    if ($attributeCode === 'category') {
                        return 'category_name_' . $categoryId;
                    } elseif ($attributeCode === 'position') {
                        return 'position_' . $categoryId;
                    } elseif ($attributeCode === 'price') {
                        return 'price_' . $categoryId . '_1';
                    }
                }
            ));
        $priceAttributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $priceAttributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('price');
        $this->indexTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('no_index');
        $this->groupRepository->expects($this->any())
            ->method('getList')
            ->willReturn($groupSearchResults);
        $this->attributeAdapterProvider->expects($this->any())
            ->method('getByAttributeCode')
            ->with($this->anything())
            ->will($this->returnCallback(
                function ($code) use (
                    $categoryAttributeMock,
                    $positionAttributeMock,
                    $priceAttributeMock
                ) {
                    static $callCount = [];
                    $callCount[$code] = !isset($callCount[$code]) ? 1 : ++$callCount[$code];

                    if ($code === 'position') {
                        return $positionAttributeMock;
                    } elseif ($code === 'category_name') {
                        return $categoryAttributeMock;
                    } elseif ($code === 'price') {
                        return $priceAttributeMock;
                    }
                }
            ));
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->with($this->anything())
            ->will($this->returnCallback(
                function ($type) use ($complexType) {
                    static $callCount = [];
                    $callCount[$type] = !isset($callCount[$type]) ? 1 : ++$callCount[$type];

                    if ($type === 'string') {
                        return 'string';
                    }
                    if ($type === 'string') {
                        return 'string';
                    } elseif ($type === 'float') {
                        return 'float';
                    } else {
                        return $complexType;
                    }
                }
            ));

        $this->assertEquals(
            $expected,
            $this->provider->getFields(['websiteId' => 1])
        );
    }

    /**
     * @return array
     */
    public function attributeProvider()
    {
        return [
            [
                'text',
                1,
                1,
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'float',
                        'store' => true
                    ]
                ]
            ],
            [
                null,
                1,
                1,
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'float',
                        'store' => true
                    ]
                ],
            ],
            [
                null,
                1,
                1,
                [
                    'category_name_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'position_1' => [
                        'type' => 'string',
                        'index' => 'no_index'
                    ],
                    'price_1_1' => [
                        'type' => 'float',
                        'store' => true
                    ]
                ]
            ]
        ];
    }
}
