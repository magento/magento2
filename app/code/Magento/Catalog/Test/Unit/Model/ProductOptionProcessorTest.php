<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

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
     * @var CustomOptionFactory|MockObject
     */
    protected $customOptionFactory;

    /**
     * @var CustomOptionInterface|MockObject
     */
    protected $customOption;

    protected function setUp(): void
    {
        $this->dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods([
                'getOptions', 'addData',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectFactory = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->dataObject);

        $this->customOption = $this->getMockBuilder(
            CustomOptionInterface::class
        )
            ->setMethods([
                'getDownloadableLinks',
            ])
            ->getMockForAbstractClass();

        $this->customOptionFactory = $this->getMockBuilder(
            CustomOptionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOptionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->customOption);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->customOptionFactory
        );

        $urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();
        $urlBuilder->expects($this->any())->method('getUrl')->willReturn('http://built.url/string/');

        $reflection = new \ReflectionClass(get_class($this->processor));
        $reflectionProperty = $reflection->getProperty('urlBuilder');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->processor, $urlBuilder);
    }

    /**
     * @param array|string $options
     * @param array $requestData
     * @dataProvider dataProviderConvertToBuyRequest
     */
    public function testConvertToBuyRequest(
        $options,
        $requestData
    ) {
        $productOptionMock = $this->getMockBuilder(ProductOptionInterface::class)
            ->getMockForAbstractClass();

        $productOptionExtensionMock = $this->getMockBuilder(
            ProductOptionExtensionInterface::class
        )
            ->setMethods([
                'getCustomOptions',
            ])
            ->getMockForAbstractClass();

        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->expects($this->any())
            ->method('getCustomOptions')
            ->willReturn($options);

        $this->dataObject->expects($this->any())
            ->method('addData')
            ->with($requestData)
            ->willReturnSelf();

        $this->assertEquals($this->dataObject, $this->processor->convertToBuyRequest($productOptionMock));
    }

    /**
     * @return array
     */
    public function dataProviderConvertToBuyRequest()
    {
        $objectManager = new ObjectManager($this);

        /** @var CustomOption $option */
        $option = $objectManager->getObject(CustomOption::class);
        $option->setOptionId(1);
        $option->setOptionValue(1);

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
     * @dataProvider dataProviderConvertToProductOption
     */
    public function testConvertToProductOption(
        $options,
        $expected
    ) {
        $this->dataObject->expects($this->any())
            ->method('getOptions')
            ->willReturn($options);

        if (!empty($options) && is_array($options)) {
            $this->customOption->expects($this->any())
                ->method('setOptionId')
                ->willReturnMap([
                    [1, $this->customOption],
                    [2, $this->customOption],
                ]);
            $this->customOption->expects($this->any())
                ->method('setOptionValue')
                ->willReturnMap([
                    [1, $this->customOption],
                    [2, $this->customOption],
                ]);
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
    public function dataProviderConvertToProductOption()
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
