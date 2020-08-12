<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Downloadable\Api\Data\DownloadableOptionInterface;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Downloadable\Model\ProductOptionProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var DownloadableOptionFactory|MockObject
     */
    protected $downloadableOptionFactory;

    /**
     * @var DownloadableOptionInterface|MockObject
     */
    protected $downloadableOption;

    protected function setUp(): void
    {
        $this->dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods([
                'getLinks', 'addData'
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

        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->downloadableOption = $this->getMockBuilder(
            DownloadableOptionInterface::class
        )
            ->setMethods([
                'getDownloadableLinks',
            ])
            ->getMockForAbstractClass();

        $this->downloadableOptionFactory = $this->getMockBuilder(
            DownloadableOptionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->downloadableOptionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->downloadableOption);

        $this->processor = new ProductOptionProcessor(
            $this->dataObjectFactory,
            $this->dataObjectHelper,
            $this->downloadableOptionFactory
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
        $productOptionMock = $this->getMockBuilder(ProductOptionInterface::class)
            ->getMockForAbstractClass();

        $productOptionExtensionMock = $this->getMockBuilder(
            ProductOptionExtensionInterface::class
        )
            ->setMethods([
                'getDownloadableOption',
            ])
            ->getMockForAbstractClass();

        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($productOptionExtensionMock);

        $productOptionExtensionMock->expects($this->any())
            ->method('getDownloadableOption')
            ->willReturn($this->downloadableOption);

        $this->downloadableOption->expects($this->any())
            ->method('getDownloadableLinks')
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
        return [
            [
                [1, 2, 3],
                [
                    'links' => [1, 2, 3],
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
            ->method('getLinks')
            ->willReturn($options);

        $this->dataObjectHelper->expects($this->any())
            ->method('populateWithArray')
            ->with(
                $this->downloadableOption,
                ['downloadable_links' => $options],
                DownloadableOptionInterface::class
            )
            ->willReturnSelf();

        $result = $this->processor->convertToProductOption($this->dataObject);

        if (!empty($expected)) {
            $this->assertArrayHasKey($expected, $result);
            $this->assertSame($this->downloadableOption, $result[$expected]);
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
                'options' => [1, 2, 3],
                'expected' => 'downloadable_option',
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
