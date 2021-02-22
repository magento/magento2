<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeOptionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeOptionProvider
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeResolver;

    /**
     * @var Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $select;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|\PHPUnit\Framework\MockObject\MockObject
     */
    private $abstractAttribute;

    /**
     * @var ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scope;

    /**
     * @var Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeResource;

    /**
     * @var OptionSelectBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $optionSelectBuilder;

    protected function setUp(): void
    {
        $this->select = $this->getMockBuilder(Select::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scope = $this->getMockBuilder(ScopeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeResource = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionSelectBuilder = $this->getMockBuilder(OptionSelectBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractAttribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getSourceModel', 'getSource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            AttributeOptionProvider::class,
            [
                'attributeResource' => $this->attributeResource,
                'scopeResolver' => $this->scopeResolver,
                'optionSelectBuilder' => $this->optionSelectBuilder,
            ]
        );
    }

    /**
     * @param array $options
     * @dataProvider getAttributeOptionsDataProvider
     */
    public function testGetAttributeOptions(array $options)
    {
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);
        
        $this->optionSelectBuilder->expects($this->any())
            ->method('getSelect')
            ->with($this->abstractAttribute, 4, $this->scope)
            ->willReturn($this->select);
        
        $this->attributeResource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn($options);

        $this->assertEquals(
            $options,
            $this->model->getAttributeOptions($this->abstractAttribute, 4)
        );
    }

    /**
     * @param array $options
     * @dataProvider optionsWithBackendModelDataProvider
     */
    public function testGetAttributeOptionsWithBackendModel(array $options)
    {
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);

        $source = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMockForAbstractClass();
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn([
                ['value' => 13, 'label' => 'Option Value for index 13'],
                ['value' => 14, 'label' => 'Option Value for index 14'],
                ['value' => 15, 'label' => 'Option Value for index 15']
            ]);
        
        $this->abstractAttribute->expects($this->any())
            ->method('getSource')
            ->willReturn($source);
        $this->abstractAttribute->expects($this->atLeastOnce())
            ->method('getSourceModel')
            ->willReturn('getSourceModel value');

        $this->optionSelectBuilder->expects($this->any())
            ->method('getSelect')
            ->with($this->abstractAttribute, 1, $this->scope)
            ->willReturn($this->select);

        $this->attributeResource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn($options);

        $this->assertEquals(
            $options,
            $this->model->getAttributeOptions($this->abstractAttribute, 1)
        );
    }

    /**
     * @return array
     */
    public function getAttributeOptionsDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'Configurable1-Black',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '13',
                        'option_title' => 'Black',
                    ],
                    [
                        'sku' => 'Configurable1-White',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '14',
                        'option_title' => 'White',
                    ],
                    [
                        'sku' => 'Configurable1-Red',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '15',
                        'option_title' => 'Red',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function optionsWithBackendModelDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'Configurable1-Black',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '13',
                        'option_title' => 'Option Value for index 13',
                        'default_title' => 'Option Value for index 13',
                    ],
                    [
                        'sku' => 'Configurable1-White',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '14',
                        'option_title' => 'Option Value for index 14',
                        'default_title' => 'Option Value for index 14',
                    ],
                    [
                        'sku' => 'Configurable1-Red',
                        'product_id' => 4,
                        'attribute_code' => 'color',
                        'value_index' => '15',
                        'option_title' => 'Option Value for index 15',
                        'default_title' => 'Option Value for index 15',
                    ],
                ],
            ],
        ];
    }
}
