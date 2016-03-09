<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns\Attributes as AttributesColumn;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface as AttributeRepository;
use Magento\Framework\View\Element\UiComponent\Processor as UiElementProcessor;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;

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
     * @var AttributeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var UiElementProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiElementProcessorMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->attributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)
            ->getMockForAbstractClass();
        $this->uiElementProcessorMock = $this->getMockBuilder(UiElementProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects(static::any())
            ->method('getProcessor')
            ->willReturn($this->uiElementProcessorMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->attributesColumn = $this->objectManagerHelper->getObject(
            AttributesColumn::class,
            [
                'context' => $this->contextMock,
                'attributeRepository' => $this->attributeRepositoryMock
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
        $attributeList = [
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
        $attributeCodes = [
            'attribute1_1_code',
            'attribute3_1_code',
            'attribute3_2_code',
            'attribute4_1_code'
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
        $this->getAttributes($attributeList, $attributeCodes);

        $this->assertSame($resultData, $this->attributesColumn->prepareDataSource($initialData));
    }

    /**
     * Set expectations for method "getAttributes"
     *
     * @param array $attributeList
     * @param array $attributeCodes
     * @return void
     */
    private function getAttributes(array $attributeList, array $attributeCodes = [])
    {
        $this->contextMock->expects(static::any())
            ->method('getRequestParam')
            ->willReturnMap(
                [
                    ['attributes_codes', [], $attributeCodes]
                ]
            );

        $this->attributeRepositoryMock->expects(static::any())
            ->method('getList')
            ->willReturn($attributeList);
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
