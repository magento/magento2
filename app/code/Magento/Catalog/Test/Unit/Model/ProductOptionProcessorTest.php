<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductOptionExtensionInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Catalog\Model\Product\Option\UrlBuilder;
use Magento\Catalog\Model\ProductOptionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var CustomOptionFactory|MockObject
     */
    protected $customOptionFactory;

    /**
     * @var CustomOptionInterface|MockObject
     */
    protected $customOption;

    protected function setUp(): void
    {
        $this->dataObject = $this->createPartialMockWithReflection(
            DataObject::class,
            ['addData', 'getData', 'setData', 'setOptions']
        );
        $dataStore = [];
        $this->dataObject->method('addData')->willReturnCallback(function ($data) use (&$dataStore) {
            $dataStore = array_merge($dataStore, $data);
            return $this->dataObject;
        });
        $this->dataObject->method('setData')->willReturnCallback(function ($key, $value = null) use (&$dataStore) {
            if (is_array($key)) {
                $dataStore = $key;
            } else {
                $dataStore[$key] = $value;
            }
            return $this->dataObject;
        });
        $this->dataObject->method('getData')->willReturnCallback(function ($key = null) use (&$dataStore) {
            return $key === null ? $dataStore : ($dataStore[$key] ?? null);
        });
        $this->dataObject->method('setOptions')->willReturnCallback(function ($value) use (&$dataStore) {
            $dataStore['options'] = $value;
            return $this->dataObject;
        });

        $this->dataObjectFactory = $this->createPartialMock(DataObjectFactory::class, ['create']);
        $this->dataObjectFactory->method('create')->willReturn($this->dataObject);

        $this->customOption = $this->createPartialMockWithReflection(
            DataObject::class,
            ['setOptionId', 'setOptionValue']
        );
        $this->customOption->method('setOptionId')->willReturnSelf();
        $this->customOption->method('setOptionValue')->willReturnSelf();

        $this->customOptionFactory = $this->createPartialMock(CustomOptionFactory::class, ['create']);
        $this->customOptionFactory->method('create')->willReturn($this->customOption);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->customOptionFactory
        );

        $urlBuilder = $this->createPartialMock(UrlBuilder::class, ['getUrl']);
        $urlBuilder->method('getUrl')->willReturn('http://built.url/string/');

        $reflection = new \ReflectionClass(get_class($this->processor));
        $reflectionProperty = $reflection->getProperty('urlBuilder');
        $reflectionProperty->setValue($this->processor, $urlBuilder);
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
        if (!empty($options)) {
            $options[0] = $options[0]($this);
        }
        $productOptionMock = $this->createMock(ProductOptionInterface::class);

        /** @var ProductOptionExtensionInterface $productOptionExtensionMock */
        $productOptionExtensionMock = $this->createPartialMockWithReflection(
            ProductOptionExtensionInterface::class,
            [
                'setCustomOptions', 'getCustomOptions',
                'getBundleOptions', 'setBundleOptions',
                'getDownloadableOption', 'setDownloadableOption',
                'getConfigurableItemOptions', 'setConfigurableItemOptions'
            ]
        );
        $customOptions = [];
        $productOptionExtensionMock->method('setCustomOptions')->willReturnCallback(
            function ($value) use (&$customOptions) {
                $customOptions = $value;
            }
        );
        $productOptionExtensionMock->method('getCustomOptions')->willReturnCallback(
            function () use (&$customOptions) {
                return $customOptions;
            }
        );
        $productOptionExtensionMock->method('getBundleOptions')->willReturn(null);
        $productOptionExtensionMock->method('setBundleOptions')->willReturnSelf();
        $productOptionExtensionMock->method('getDownloadableOption')->willReturn(null);
        $productOptionExtensionMock->method('setDownloadableOption')->willReturnSelf();
        $productOptionExtensionMock->method('getConfigurableItemOptions')->willReturn(null);
        $productOptionExtensionMock->method('setConfigurableItemOptions')->willReturnSelf();

        $productOptionMock->method('getExtensionAttributes')->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->setCustomOptions($options);

        $this->dataObject->addData($requestData);

        $this->assertEquals($this->dataObject, $this->processor->convertToBuyRequest($productOptionMock));
    }

    protected function getOptionsDataForprovider()
    {
        $objectManager = new ObjectManager($this);

        /** @var CustomOption $option */
        $option = $objectManager->getObject(CustomOption::class);
        $option->setOptionId(1);
        $option->setOptionValue(1);
        return $option;
    }

    /**
     * @return array
     */
    public static function dataProviderConvertToBuyRequest()
    {

        /** @var CustomOption $option */
        $option = static fn (self $testCase) => $testCase->getOptionsDataForprovider();

        return [
            [
                [$option],
                [
                    'options' => [
                        1 => 1,
                    ],
                ],
            ],
            [[], []],
            ['', []],
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
        $this->dataObject->setOptions($options);

        if (!empty($options) && is_array($options)) {
            // Set up the custom option behavior
            $this->customOption->setOptionId(1);
            $this->customOption->setOptionId(2);
            $this->customOption->setOptionValue(1);
            $this->customOption->setOptionValue(2);
        }

        $result = $this->processor->convertToProductOption($this->dataObject);

        if (!empty($expected)) {
            $this->assertArrayHasKey($expected, $result);
            $this->assertIsArray($result);
            $this->assertSame($this->customOption, $result['custom_options'][0]);
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
                    2 => [
                        1,
                        2,
                        'url' => [
                            'route' => 'route',
                            'params' => ['id' => 20, 'key' => '8175c7c36ef69432347e']
                        ]
                    ],
                ],
                'expected' => 'custom_options',
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
