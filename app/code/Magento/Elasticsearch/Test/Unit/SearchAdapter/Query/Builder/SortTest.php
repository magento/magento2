<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var Sort
     */
    private $sortBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeAdapterProvider = $this->getMockBuilder(AttributeProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByAttributeCode'])
            ->getMock();
        $this->fieldNameResolver = $this->getMockBuilder(FieldNameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldName'])
            ->getMock();

        $this->sortBuilder = (new ObjectManager($this))->getObject(
            Sort::class,
            [
                'attributeAdapterProvider' => $this->attributeAdapterProvider,
                'fieldNameResolver' => $this->fieldNameResolver,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @dataProvider getSortProvider
     * @param array $sortItems
     * @param $isSortable
     * @param $isFloatType
     * @param $isIntegerType
     * @param $fieldName
     * @param array $expected
     */
    public function testGetSort(
        array $sortItems,
        $isSortable,
        $isFloatType,
        $isIntegerType,
        $isComplexType,
        $fieldName,
        array $expected
    ) {
        /** @var MockObject|RequestInterface $request */
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSort'])
            ->getMockForAbstractClass();
        $request->expects($this->any())
            ->method('getSort')
            ->willReturn($sortItems);
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSortable', 'isFloatType', 'isIntegerType', 'isComplexType'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isSortable')
            ->willReturn($isSortable);
        $attributeMock->expects($this->any())
            ->method('isFloatType')
            ->willReturn($isFloatType);
        $attributeMock->expects($this->any())
            ->method('isIntegerType')
            ->willReturn($isIntegerType);
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $this->attributeAdapterProvider->expects($this->any())
            ->method('getByAttributeCode')
            ->with($this->anything())
            ->willReturn($attributeMock);
        $this->fieldNameResolver->expects($this->any())
            ->method('getFieldName')
            ->with($this->anything())
            ->willReturnCallback(
                function ($attribute, $context) use ($fieldName) {
                    if (empty($context)) {
                        return $fieldName;
                    } elseif ($context['type'] === 'sort') {
                        return 'sort_' . $fieldName;
                    }
                }
            );

        $this->assertEquals(
            $expected,
            $this->sortBuilder->getSort($request)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getSortProvider()
    {
        return [
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ]
                ],
                false,
                false,
                false,
                false,
                null,
                []
            ],
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ],
                    [
                        'field' => 'price',
                        'direction' => 'DESC'
                    ],
                ],
                false,
                false,
                false,
                false,
                'price',
                [
                    [
                        'price' => [
                            'order' => 'desc'
                        ]
                    ]
                ]
            ],
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ],
                    [
                        'field' => 'price',
                        'direction' => 'DESC'
                    ],
                ],
                true,
                true,
                true,
                false,
                'price',
                [
                    [
                        'price' => [
                            'order' => 'desc'
                        ]
                    ]
                ]
            ],
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ],
                    [
                        'field' => 'name',
                        'direction' => 'DESC'
                    ],
                ],
                true,
                false,
                false,
                false,
                'name',
                [
                    [
                        'name.sort_name' => [
                            'order' => 'desc'
                        ]
                    ]
                ]
            ],
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ],
                    [
                        'field' => 'not_eav_attribute',
                        'direction' => 'DESC'
                    ],
                ],
                false,
                false,
                false,
                false,
                'not_eav_attribute',
                [
                    [
                        'not_eav_attribute' => [
                            'order' => 'desc'
                        ]
                    ]
                ]
            ],
            [
                [
                    [
                        'field' => 'entity_id',
                        'direction' => 'DESC'
                    ],
                    [
                        'field' => 'color',
                        'direction' => 'DESC'
                    ],
                ],
                true,
                false,
                false,
                true,
                'color',
                [
                    [
                        'color_value.sort_color' => [
                            'order' => 'desc'
                        ]
                    ]
                ]
            ]
        ];
    }
}
