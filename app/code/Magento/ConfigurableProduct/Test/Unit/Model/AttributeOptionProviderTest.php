<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeOptionProviderTest extends TestCase
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
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolver;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $abstractAttribute;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var Attribute|MockObject
     */
    private $attributeResource;

    /**
     * @var OptionSelectBuilderInterface|MockObject
     */
    private $optionSelectBuilder;

    protected function setUp(): void
    {
        $this->select = $this->createMock(Select::class);

        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->scope = $this->createMock(ScopeInterface::class);

        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);

        $this->attributeResource = $this->createMock(Attribute::class);

        $this->optionSelectBuilder = $this->createMock(OptionSelectBuilderInterface::class);

        $this->abstractAttribute = $this->createMock(AbstractAttribute::class);

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
     */
    #[DataProvider('getAttributeOptionsDataProvider')]
    public function testGetAttributeOptions(array $options)
    {
        $this->scopeResolver->method('getScope')->willReturn($this->scope);

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
     */
    #[DataProvider('optionsWithBackendModelDataProvider')]
    public function testGetAttributeOptionsWithBackendModel(array $options)
    {
        $this->scopeResolver->method('getScope')->willReturn($this->scope);

        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn([
                ['value' => 13, 'label' => 'Option Value for index 13'],
                ['value' => 14, 'label' => 'Option Value for index 14'],
                ['value' => 15, 'label' => 'Option Value for index 15']
            ]);

        $this->abstractAttribute->method('getSource')->willReturn($source);
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
    public static function getAttributeOptionsDataProvider()
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
    public static function optionsWithBackendModelDataProvider()
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
