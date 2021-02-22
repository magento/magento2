<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Builder;
use Magento\Downloadable\Helper\Download;

/**
 * Class BuilderTest
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $downloadFileMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $objectCopyServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var Builder
     */
    private $service;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $mockComponentFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $linkMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->downloadFileMock = $this->getMockBuilder(
            \Magento\Downloadable\Helper\File::class
        )->disableOriginalConstructor()->getMock();

        $this->objectCopyServiceMock = $this->getMockBuilder(
            \Magento\Framework\DataObject\Copy::class
        )->disableOriginalConstructor()->getMock();

        $this->dataObjectHelperMock = $this->getMockBuilder(
            \Magento\Framework\Api\DataObjectHelper::class
        )->disableOriginalConstructor()->getMock();

        $this->mockComponentFactory = $this->getMockBuilder(\Magento\Downloadable\Model\LinkFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->linkMock = $this->getMockBuilder(LinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        
        $this->service = $objectManagerHelper->getObject(
            Builder::class,
            [
                'downloadableFile' => $this->downloadFileMock,
                'objectCopyService' => $this->objectCopyServiceMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'componentFactory' => $this->mockComponentFactory
            ]
        );
    }

    /**
     * @dataProvider buildProvider
     * @param array $data
     * @param float $expectedPrice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testBuild($data, $expectedPrice)
    {
        $downloadableData = ['sort_order' => 1];
        $baseTmpPath = 'l/2/e/f/gm';
        $baseSampleTmpPath = 's/l/2/e/f/gm';
        $basePath = 'l/e/f/gm';
        $baseSamplePath = 's/l/e/f/gm';
        $linkFileName = 'cat1.png';
        $this->objectCopyServiceMock->expects($this->exactly(2))->method('getDataFromFieldset')->withConsecutive(
            [
                'downloadable_data',
                'to_link',
                $data
            ],
            [
                'downloadable_link_sample_data',
                'to_link_sample',
                $data['sample']
            ]
        )->willReturn($downloadableData);
        $this->service->setData($data);
        $this->dataObjectHelperMock->method('populateWithArray')
            ->withConsecutive(
                [
                    $this->linkMock,
                    array_merge(
                        $data,
                        $downloadableData
                    ),
                    LinkInterface::class
                ],
                [
                    $this->linkMock,
                    array_merge(
                        $data,
                        $downloadableData,
                        $data['sample']
                    ),
                    LinkInterface::class
                ]
            )->willReturn($this->linkMock);
        $this->linkMock->expects($this->once())->method('getLinkType')->willReturn(Download::LINK_TYPE_FILE);
        $linkModel = $this->getMockBuilder(Link::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockComponentFactory->expects($this->once())->method('create')->willReturn($linkModel);
        $linkModel->expects($this->once())->method('getBaseTmpPath')->willReturn($baseTmpPath);
        $linkModel->expects($this->once())->method('getBaseSampleTmpPath')->willReturn($baseSampleTmpPath);
        $linkModel->expects($this->once())->method('getBasePath')->willReturn($basePath);
        $linkModel->expects($this->once())->method('getBaseSamplePath')->willReturn($baseSamplePath);
        $this->downloadFileMock->expects($this->exactly(2))
            ->method('moveFileFromTmp')
            ->withConsecutive(
                [
                    $baseTmpPath,
                    $basePath,
                    $data['file']
                ],
                [
                    $baseSampleTmpPath,
                    $baseSamplePath,
                    $data['sample']['file']
                ]
            )->willReturn($linkFileName);
        $this->linkMock->expects($this->once())->method('setLinkFile')->with($linkFileName);
        $this->linkMock->expects($this->once())->method('setLinkUrl')->with(null);
        $this->linkMock->expects($this->once())->method('getSampleType')->willReturn(Download::LINK_TYPE_FILE);
        $this->linkMock->expects($this->once())->method('setSampleFile')->with($linkFileName);
        if (!isset($data['sort_order'])) {
            $this->linkMock->expects($this->once())->method('setSortOrder')->with(1);
        }
        if (isset($data['is_unlimited'])) {
            $this->linkMock->expects($this->once())->method('setNumberOfDownloads')->with(0);
        }
        if (isset($data['price'])) {
            $this->linkMock->expects($this->once())->method('getPrice')->willReturn($data['price']);
        } else {
            $this->linkMock->expects($this->once())->method('setPrice')->with($expectedPrice);
        }

        $this->service->build($this->linkMock);
    }

    /**
     */
    public function testBuildFileNotProvided()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Link file not provided');

        $data = [
            'type' => 'file',
            'sample' => [
                'file' => 'cXVlIHRhbA==',
                'type' => 'file'
            ]
        ];
        $downloadableData = ['sort_order' => 1];
        $this->objectCopyServiceMock->expects($this->once())->method('getDataFromFieldset')->withConsecutive(
            [
                'downloadable_data',
                'to_link',
                $data
            ]
        )->willReturn($downloadableData);
        $this->service->setData($data);
        $this->dataObjectHelperMock->method('populateWithArray')
            ->with(
                $this->linkMock,
                array_merge(
                    $data,
                    $downloadableData
                ),
                LinkInterface::class
            )->willReturn($this->linkMock);
        $this->linkMock->expects($this->once())->method('getLinkType')->willReturn(Download::LINK_TYPE_FILE);
        $this->downloadFileMock->expects($this->never())
            ->method('moveFileFromTmp');

        $this->service->build($this->linkMock);
    }

    /**
     * @return array
     */
    public function buildProvider()
    {
        $expectedPrice = 0;
        $expectedOrder = 1;
        return [
            'price_0' => [
                [
                    'file' => 'cXVlIHRhbA==',
                    'type' => 'file',
                    'sample' => [
                        'file' => 'cXVlIHRhbA==',
                        'type' => 'file'
                    ]
                ],
                'expectedPrice' => $expectedPrice,
                'expectedOrder' => $expectedOrder
            ],
            'price_declared' => [
                [
                    'file' => 'cXVlIHRhbA==',
                    'type' => 'file',
                    'price' => 150,
                    'sort_order' => 2,
                    'is_unlimited' => true,
                    'sample' => [
                        'file' => 'cXVlIHRhbA==',
                        'type' => 'file'
                    ]
                ],
                'expectedPrice' => 150,
                'expectedOrder' => 2
            ]
        ];
    }
}
