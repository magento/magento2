<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\AttributeOptionProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
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
        $this->attributeResource = $this->createMock(Attribute::class);
        $this->optionSelectBuilder = $this->createMock(OptionSelectBuilderInterface::class);
        $this->abstractAttribute = $this->createMock(AbstractAttribute::class);

        $this->model = new AttributeOptionProvider($this->attributeResource, $this->optionSelectBuilder);
    }

    /**
     * @param array $options
     * @dataProvider getAttributeOptionsDataProvider
     */
    public function testGetAttributeOptions(array $options)
    {
        $this->optionSelectBuilder->expects($this->once())
            ->method('getSelect')
            ->with($this->abstractAttribute, 4)
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
        $source = $this->createMock(AbstractSource::class);
        $source->expects($this->once())
            ->method('getAllOptions')
            ->willReturn([
                ['value' => 13, 'label' => 'Option Value for index 13'],
                ['value' => 14, 'label' => 'Option Value for index 14'],
                ['value' => 15, 'label' => 'Option Value for index 15']
            ]);

        $this->abstractAttribute->expects($this->atLeastOnce())
            ->method('getSource')
            ->willReturn($source);
        $this->abstractAttribute->expects($this->atLeastOnce())
            ->method('getSourceModel')
            ->willReturn('getSourceModel value');

        $this->optionSelectBuilder->expects($this->once())
            ->method('getSelect')
            ->with($this->abstractAttribute, 1)
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
