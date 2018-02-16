<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Attributes test.
 */
class AttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager.
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * Attributes block.
     *
     * @var \Magento\Catalog\Block\Product\View\Attributes
     */
    private $block;

    /**
     * Context model.
     *
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Registry.
     *
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * Currency price convert/format model.
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * Product model.
     *
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getRegistry')
            ->willReturn($this->registry);

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributes', 'hasData'])
            ->getMock();

        $this->block = new \Magento\Catalog\Block\Product\View\Attributes(
            $this->context,
            $this->registry,
            $this->priceCurrency
        );

        $this->getObjectManager()->setBackwardCompatibleProperty($this->block, '_product', $this->product);
    }

    /**
     * @covers \Magento\Catalog\Block\Product\View\Attributes::getAdditionalData
     * @dataProvider getAdditionalDataProvider
     *
     * @param array $attributes
     * @param bool $productHasAttributeValue
     * @param array $excludedAttributes
     * @param array $expectedResult
     * @return void
     */
    public function testGetAdditionalData(
        $attributes,
        $productHasAttributeValue,
        $excludedAttributes,
        $expectedResult
    ) {
        $this->product->expects(self::once())->method('getAttributes')
            ->willReturn($attributes);

        $this->product->expects(self::any())->method('hasData')
            ->with('attribute')
            ->willReturn($productHasAttributeValue);

        $this->priceCurrency->expects(self::any())
            ->method('convertAndFormat')
            ->withAnyParameters()
            ->willReturn('test');

        self::assertEquals($expectedResult, $this->block->getAdditionalData($excludedAttributes));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function getAdditionalDataProvider()
    {
        return [
            'No Attributes' => [
                [],
                false,
                [],
                []
            ],
            'With Invisible On Frontend Attribute' => [
                [
                    $this->prepareAttributeMock(['is_visible_on_front' => false])
                ],
                false,
                [],
                []
            ],
            'With Excluded On Frontend Attribute' => [
                [
                    $this->prepareAttributeMock(
                        [
                            'attribute_code' => 'excluded_attribute',
                            'is_visible_on_front' => false
                        ]
                    )
                ],
                false,
                ['excluded_attribute'],
                []
            ],
            'Product Has No Attribute Value' => [
                [
                    $this->prepareAttributeMock(
                        [
                            'attribute_code' => 'attribute',
                            'store_label' => 'Test Attribute',
                            'is_visible_on_front' => true,
                        ]
                    )
                ],
                false,
                [],
                [
                    'attribute' => [
                        'label' => 'Test Attribute',
                        'value' => 'N/A',
                        'code' => 'attribute',
                    ]
                ]
            ],
            'Product With Null Attribute Value' => [
                [
                    $this->prepareAttributeMock(
                        [
                            'attribute_code' => 'attribute',
                            'store_label' => 'Test Attribute',
                            'is_visible_on_front' => true,
                            'value' => null
                        ]
                    )
                ],
                true,
                [],
                [
                    'attribute' => [
                        'label' => 'Test Attribute',
                        'value' => 'No',
                        'code' => 'attribute',
                    ]
                ]
            ],
            'Product With Price Attribute' => [
                [
                    $this->prepareAttributeMock(
                        [
                            'attribute_code' => 'attribute',
                            'store_label' => 'Test Attribute',
                            'is_visible_on_front' => true,
                            'frontend_input' => 'price',
                            'value' => '2.1'
                        ]
                    )
                ],
                true,
                [],
                [
                    'attribute' => [
                        'label' => 'Test Attribute',
                        'value' => 'test',
                        'code' => 'attribute',
                    ]
                ]
            ],
            'Product With Phrase Attribute Value' => [
                [
                    $this->prepareAttributeMock(
                        [
                            'attribute_code' => 'attribute',
                            'store_label' => 'Test Attribute',
                            'is_visible_on_front' => true,
                            'frontend_input' => 'price',
                            'value' => __('test')
                        ]
                    )
                ],
                true,
                [],
                [
                    'attribute' => [
                        'label' => 'Test Attribute',
                        'value' => 'test',
                        'code' => 'attribute',
                    ]
                ]
            ],
        ];
    }

    /**
     * Return object manager.
     *
     * @return ObjectManager
     */
    private function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = new ObjectManager($this);
        }

        return $this->objectManager;
    }

    /**
     * Prepare attribute mock.
     *
     * @param array $data
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    private function prepareAttributeMock($data = [])
    {
        $attributeValue = isset($data['value']) ? $data['value']: null;

        /** @var \PHPUnit_Framework_MockObject_MockObject $frontendModel */
        $frontendModel = $this->getMockBuilder(
            \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $frontendModel->expects(self::any())
            ->method('getValue')
            ->willReturn($attributeValue);

        $attribute = $this->getObjectManager()->getObject(
            \Magento\Eav\Model\Entity\Attribute::class,
            ['data' => $data]
        );
        $this->getObjectManager()->setBackwardCompatibleProperty($attribute, '_frontend', $frontendModel);

        return $attribute;
    }
}
