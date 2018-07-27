<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Api\Data\DownloadableOptionInterface;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Downloadable\Model\ProductOptionProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessorTest extends \PHPUnit_Framework_TestCase
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
     * @var DataObjectHelper | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var DownloadableOptionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadableOptionFactory;

    /**
     * @var DownloadableOptionInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadableOption;

    protected function setUp()
    {
        $this->dataObject = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods([
                'getLinks',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectFactory = $this->getMockBuilder('Magento\Framework\DataObject\Factory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->dataObject);

        $this->dataObjectHelper = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->downloadableOption = $this->getMockBuilder(
            'Magento\Downloadable\Api\Data\DownloadableOptionInterface'
        )
            ->setMethods([
                'getDownloadableLinks',
            ])
            ->getMockForAbstractClass();

        $this->downloadableOptionFactory = $this->getMockBuilder(
            'Magento\Downloadable\Model\DownloadableOptionFactory'
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
        $productOptionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductOptionInterface')
            ->getMockForAbstractClass();

        $productOptionExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductOptionExtensionInterface')
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
                'Magento\Downloadable\Api\Data\DownloadableOptionInterface'
            )
            ->willReturnSelf();

        $result = $this->processor->convertToProductOption($this->dataObject);

        if (!empty($expected)) {
            $this->assertArrayHasKey($expected, $result);
            $this->assertSame($this->downloadableOption, $result[$expected]);
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
