<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Bundle\Model\BundleOption;
use Magento\Bundle\Model\ProductOptionProcessor;
use Magento\Catalog\Api\Data\ProductOptionExtensionInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductOptionProcessorTest extends TestCase
{
    use MockCreationTrait;

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
     * @var BundleOptionInterfaceFactory|MockObject
     */
    protected $bundleOptionInterfaceFactory;

    /**
     * @var BundleOptionInterface|MockObject
     */
    protected $bundleOption;

    protected function setUp(): void
    {
        $this->dataObject = new DataObject();

        $this->dataObjectFactory = $this->createPartialMock(DataObjectFactory::class, ['create']);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObject);

        $this->bundleOption = $this->createMock(BundleOptionInterface::class);

        $this->bundleOptionInterfaceFactory = $this->createPartialMock(
            BundleOptionInterfaceFactory::class,
            ['create']
        );
        $this->bundleOptionInterfaceFactory->method('create')->willReturn($this->bundleOption);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->bundleOptionInterfaceFactory
        );
    }

    /**
     * @param array|string $options
     * @param array $requestData
     */
    #[DataProvider('dataProviderConvertToBuyRequest')]
    public function testConvertToBuyRequest(
        $options,
        $requestData
    ) {
        if (!empty($options) && is_callable($options[0])) {
            $options[0] = $options[0]($this);
        }
        $productOptionMock = $this->createMock(ProductOptionInterface::class);

        $productOptionExtensionMock = $this->createPartialMockWithReflection(
            ProductOptionExtensionInterface::class,
            ['getBundleOptions', 'setBundleOptions']
        );
        $productOptionExtensionMock->method('getBundleOptions')->willReturn($options);

        $productOptionMock->method('getExtensionAttributes')->willReturn($productOptionExtensionMock);

        $this->dataObject->addData($requestData);

        $this->assertEquals($this->dataObject, $this->processor->convertToBuyRequest($productOptionMock));
    }

    protected function getObjectForBundleOptionClass()
    {
        $objectManager = new ObjectManager($this);

        /** @var BundleOption $option */
        $option = $objectManager->getObject(BundleOption::class);
        $option->setOptionId(1);
        $option->setOptionQty(1);
        $option->setOptionSelections(['selection']);
        return $option;
    }

    /**
     * @return array
     */
    public static function dataProviderConvertToBuyRequest()
    {
        $option = static fn (self $testCase) => $testCase->getObjectForBundleOptionClass();
        return [
            [
                [$option],
                [
                    'bundle_option' => [
                        1 => ['selection'],
                    ],
                    'bundle_option_qty' => [
                        1 => 1,
                    ],
                ],
            ],
            [[], []],
            ['is not array', []],
        ];
    }

    /**
     * @param array|string $options
     * @param array|int $optionsQty
     * @param string|null $expected
     */
    #[DataProvider('dataProviderConvertToProductOption')]
    public function testConvertToProductOption(
        $options,
        $optionsQty,
        $expected
    ) {
        $this->dataObject->setBundleOption($options);
        $this->dataObject->setBundleOptionQty($optionsQty);

        if (!empty($options) && is_array($options)) {
            $this->bundleOption->expects($this->any())
                ->method('setOptionId')
                ->willReturnMap([
                    [1, $this->bundleOption],
                    [2, $this->bundleOption],
                ]);
            $this->bundleOption->expects($this->any())
                ->method('setOptionSelections')
                ->willReturnMap([
                    [$options[1], $this->bundleOption],
                    [$options[2], $this->bundleOption],
                ]);
            $this->bundleOption->expects($this->any())
                ->method('setOptionQty')
                ->willReturnMap([
                    [1, $this->bundleOption],
                ]);
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
                    1 => ['selection1'],
                    2 => ['selection2'],
                    3 => [],
                    4 => '',
                ],
                'optionsQty' => [
                    1 => 1,
                ],
                'expected' => 'bundle_options',
            ],
            [
                'options' => [],
                'optionsQty' => 0,
                'expected' => null,
            ],
            [
                'options' => 'is not array',
                'optionsQty' => 0,
                'expected' => null,
            ],
        ];
    }
}
