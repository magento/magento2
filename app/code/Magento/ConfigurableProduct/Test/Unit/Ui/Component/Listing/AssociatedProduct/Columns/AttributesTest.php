<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns\Attributes as AttributesColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\Processor as UiElementProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributesColumn
     */
    private $attributesColumn;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var UiElementProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiElementProcessorMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var ProductAttributeSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->attributeRepositoryMock = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiElementProcessorMock = $this->getMockBuilder(UiElementProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsMock = $this->getMockBuilder(ProductAttributeSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects(static::never())
            ->method('getProcessor')
            ->willReturn($this->uiElementProcessorMock);
        $this->searchCriteriaBuilderMock->expects(static::any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects(static::any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->attributesColumn = $this->objectManagerHelper->getObject(
            AttributesColumn::class,
            [
                'context' => $this->contextMock,
                'attributeRepository' => $this->attributeRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    public function testPrepareDataSource()
    {
        $name = 'some_name';
        $initialData = [
            'data' => [
                'items' => [
                    ['attribute1_1_code' => 'attribute1_1_option2'],
                    ['attribute2_1_code' => 'attribute2_1_option3'],
                    ['attribute3_1_code' => 'attribute3_1_option3', 'attribute3_2_code' => 'attribute3_2_option1']
                ]
            ]
        ];
        $attributes = [
            $this->createAttributeMock(
                'attribute1_1_code',
                'attribute1_1_label',
                [
                    $this->createAttributeOptionMock('attribute1_1_option1', 'attribute1_1_option1_label'),
                    $this->createAttributeOptionMock('attribute1_1_option2', 'attribute1_1_option2_label')
                ]
            ),
            $this->createAttributeMock(
                'attribute2_1_code',
                'attribute2_1_label',
                [
                    $this->createAttributeOptionMock('attribute2_1_option1', 'attribute2_1_option1_label'),
                    $this->createAttributeOptionMock('attribute2_1_option2', 'attribute2_1_option2_label')
                ]
            ),
            $this->createAttributeMock(
                'attribute3_1_code',
                'attribute3_1_label',
                [
                    $this->createAttributeOptionMock('attribute3_1_option1', 'attribute3_1_option1_label'),
                    $this->createAttributeOptionMock('attribute3_1_option2', 'attribute3_1_option2_label'),
                    $this->createAttributeOptionMock('attribute3_1_option3', 'attribute3_1_option3_label')
                ]
            ),
            $this->createAttributeMock(
                'attribute3_2_code',
                'attribute3_2_label',
                [
                    $this->createAttributeOptionMock('attribute3_2_option1', 'attribute3_2_option1_label'),
                    $this->createAttributeOptionMock('attribute3_2_option2', 'attribute3_2_option2_label'),
                    $this->createAttributeOptionMock('attribute3_2_option3', 'attribute3_2_option3_label')
                ]
            ),
            $this->createAttributeMock(
                'attribute4_1_code',
                'attribute4_1_label'
            )
        ];
        $resultData = [
            'data' => [
                'items' => [
                    [
                        'attribute1_1_code' => 'attribute1_1_option2',
                        $name => 'attribute1_1_label: attribute1_1_option2_label'
                    ],
                    [
                        'attribute2_1_code' => 'attribute2_1_option3',
                        $name => ''
                    ],
                    [
                        'attribute3_1_code' => 'attribute3_1_option3',
                        'attribute3_2_code' => 'attribute3_2_option1',
                        $name => 'attribute3_1_label: attribute3_1_option3_label,'
                            . ' attribute3_2_label: attribute3_2_option1_label'
                    ]
                ]
            ]
        ];

        $this->attributesColumn->setData('name', $name);

        $this->attributeRepositoryMock->expects(static::any())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->searchResultsMock->expects(static::any())
            ->method('getItems')
            ->willReturn($attributes);

        $this->assertSame($resultData, $this->attributesColumn->prepareDataSource($initialData));
    }

    /**
     * Create product attribute mock object
     *
     * @param string $attributeCode
     * @param string $defaultFrontendLabel
     * @param array $options
     * @return ProductAttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($attributeCode, $defaultFrontendLabel, array $options = [])
    {
        $attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->getMockForAbstractClass();

        $attributeMock->expects(static::any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects(static::any())
            ->method('getDefaultFrontendLabel')
            ->willReturn($defaultFrontendLabel);
        $attributeMock->expects(static::any())
            ->method('getOptions')
            ->willReturn($options);

        return $attributeMock;
    }

    /**
     * Create attribute option mock object
     *
     * @param string $value
     * @param string $label
     * @return AttributeOptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeOptionMock($value, $label)
    {
        $attributeOptionMock = $this->getMockBuilder(AttributeOptionInterface::class)
            ->getMockForAbstractClass();

        $attributeOptionMock->expects(static::any())
            ->method('getValue')
            ->willReturn($value);
        $attributeOptionMock->expects(static::any())
            ->method('getLabel')
            ->willReturn($label);

        return $attributeOptionMock;
    }
}
