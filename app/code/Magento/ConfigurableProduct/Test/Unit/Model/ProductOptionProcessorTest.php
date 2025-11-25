<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Quote\Test\Unit\Helper\ProductOptionExtensionTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductOptionExtensionInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\ProductOptionProcessor;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValue;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductOptionProcessorTest extends TestCase
{
    /**
     * @var ProductOptionProcessor
     */
    protected $processor;

    /**
     * @var DataObject|MockObject
     */
    protected $dataObject;

    /**
     * @var DataObjectFactory|MockObject
     */
    protected $dataObjectFactory;

    /**
     * @var ConfigurableItemOptionValueFactory|MockObject
     */
    protected $itemOptionValueFactory;

    /**
     * @var ConfigurableItemOptionValueInterface|MockObject
     */
    protected $itemOptionValue;

    protected function setUp(): void
    {
        $this->dataObject = new DataObjectTestHelper();

        $this->dataObjectFactory = $this->createPartialMock(DataObjectFactory::class, ['create']);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObject);

        $this->itemOptionValue = $this->createMock(ConfigurableItemOptionValueInterface::class);

        $this->itemOptionValueFactory = $this->createPartialMock(
            ConfigurableItemOptionValueFactory::class,
            ['create']
        );
        $this->itemOptionValueFactory->method('create')->willReturn($this->itemOptionValue);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->itemOptionValueFactory
        );
    }

    /**
     * @param array|string $options
     * @param array $requestData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[DataProvider('dataProviderConvertToBuyRequest')]
    public function testConvertToBuyRequest(
        $options,
        $requestData
    ) {
        if (!empty($options[0]) && is_callable($options[0])) {
            $options[0] = $options[0]($this);
        }
        $productOptionMock = $this->createMock(ProductOptionInterface::class);

        $productOptionExtensionMock = new ProductOptionExtensionTestHelper();
        $productOptionMock->method('getExtensionAttributes')->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->setConfigurableItemOptions($options);

        $this->assertEquals($this->dataObject, $this->processor->convertToBuyRequest($productOptionMock));
    }

    protected function getMockForOptionClass()
    {
        $objectManager = new ObjectManager($this);

        /** @var ConfigurableItemOptionValue $option */
        $option = $objectManager->getObject(
            ConfigurableItemOptionValue::class
        );
        $option->setOptionId(1);
        $option->setOptionValue('test');

        return $option;
    }

    /**
     * @return array
     */
    public static function dataProviderConvertToBuyRequest()
    {
        $option = static fn (self $testCase) => $testCase->getMockForOptionClass();

        return [
            [
                [$option],
                [
                    'super_attribute' => [
                        1 => 'test',
                    ],
                ],
            ],
            [[], []],
        ];
    }

    /**
     * @param array|string $options
     * @param string|null $expected
     */
    #[DataProvider('dataProviderConvertToProductOption')]
    public function testConvertToProductOption(
        $options,
        $expected
    ) {
        $this->dataObject->setSuperAttribute($options);

        if (!empty($options) && is_array($options)) {
            $this->itemOptionValue->expects($this->any())
                ->method('setOptionId')
                ->with(1)
                ->willReturnSelf();
            $this->itemOptionValue->expects($this->any())
                ->method('setOptionValue')
                ->with($options[1])
                ->willReturnSelf();
        }

        $result = $this->processor->convertToProductOption($this->dataObject);

        if (!empty($expected)) {
            $this->assertArrayHasKey($expected, $result);
            $this->assertIsArray($result[$expected]);
        } else {
            $this->assertEmpty($result);
        }
    }

    /**
     * @return array
     */
    public static function dataProviderConvertToProductOption()
    {
        return [
            [
                'options' => [
                    1 => 'value',
                ],
                'expected' => 'configurable_item_options',
            ],
            [
                'options' => [],
                'expected' => null,
            ],
            [
                'options' => 'is not array',
                'expected' => null,
            ],
        ];
    }
}
