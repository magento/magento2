<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Bundle\Model\ProductOptionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductOptionProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductOptionProcessor
     */
    protected $processor;

    /**
     * @var DataObject | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObject;

    /**
     * @var DataObjectFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectFactory;

    /**
     * @var BundleOptionInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleOptionInterfaceFactory;

    /**
     * @var BundleOptionInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleOption;

    protected function setUp()
    {
        $this->dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods([
                'getBundleOption',
                'getBundleOptionQty',
                'create',
                'addData'
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

        $this->bundleOption = $this->getMockBuilder(
            \Magento\Bundle\Api\Data\BundleOptionInterface::class
        )
            ->getMockForAbstractClass();

        $this->bundleOptionInterfaceFactory = $this->getMockBuilder(
            \Magento\Bundle\Api\Data\BundleOptionInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleOptionInterfaceFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->bundleOption);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->bundleOptionInterfaceFactory
        );
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
        $productOptionMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->getMockForAbstractClass();

        $productOptionExtensionMock = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductOptionExtensionInterface::class
        )->setMethods(['getBundleOptions'])->getMockForAbstractClass();

        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->expects($this->any())
            ->method('getBundleOptions')
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

        /** @var \Magento\Bundle\Model\BundleOption $option */
        $option = $objectManager->getObject(\Magento\Bundle\Model\BundleOption::class);
        $option->setOptionId(1);
        $option->setOptionQty(1);
        $option->setOptionSelections(['selection']);

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
     * @dataProvider dataProviderConvertToProductOption
     */
    public function testConvertToProductOption(
        $options,
        $optionsQty,
        $expected
    ) {
        $this->dataObject->expects($this->any())
            ->method('getBundleOption')
            ->willReturn($options);
        $this->dataObject->expects($this->any())
            ->method('getBundleOptionQty')
            ->willReturn($optionsQty);

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
            $this->assertTrue(is_array($result[$expected]));
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
                    1 => ['selection1'],
                    2 => ['selection2'],
                    3 => [],
                    4 => '',
                ],
                'options_qty' => [
                    1 => 1,
                ],
                'expected' => 'bundle_options',
            ],
            [
                'options' => [],
                'options_qty' => 0,
                'expected' => null,
            ],
            [
                'options' => 'is not array',
                'options_qty' => 0,
                'expected' => null,
            ],
        ];
    }
}
