<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sample;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Model\Sample\Builder;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for downloadable products' builder sample class
 */
class BuilderTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $downloadFileMock;

    /**
     * @var MockObject
     */
    private $objectCopyServiceMock;

    /**
     * @var MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var Builder
     */
    private $service;

    /**
     * @var MockObject
     */
    private $mockComponentFactory;

    /**
     * @var MockObject
     */
    private $sampleMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->downloadFileMock = $this->getMockBuilder(
            File::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->objectCopyServiceMock = $this->getMockBuilder(
            Copy::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelperMock = $this->getMockBuilder(
            DataObjectHelper::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->mockComponentFactory = $this->getMockBuilder(SampleFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->sampleMock = $this->getMockBuilder(SampleInterface::class)
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

    public function testBuild()
    {
        $data = [
            'file' => 'cXVlIHRhbA==',
            'use_default_title' => '1',
            'type' => 'file'
        ];
        $downloadableData = ['sort_order' => 1];
        $baseTmpPath = 'l/2/e/f/gm';
        $basePath = 'l/e/f/gm';
        $fileName = 'cat1.png';
        $this->objectCopyServiceMock->expects($this->once())->method('getDataFromFieldset')->with(
            'downloadable_data',
            'to_sample',
            $data
        )->willReturn($downloadableData);
        $this->dataObjectHelperMock->method('populateWithArray')
            ->with(
                $this->sampleMock,
                array_merge(
                    $data,
                    $downloadableData
                ),
                SampleInterface::class
            )->willReturn($this->sampleMock);
        $this->sampleMock->expects($this->once())->method('getSampleType')->willReturn(Download::LINK_TYPE_FILE);
        $sampleModel = $this->getMockBuilder(Sample::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockComponentFactory->expects($this->once())->method('create')->willReturn($sampleModel);
        $sampleModel->expects($this->once())->method('getBaseTmpPath')->willReturn($baseTmpPath);
        $sampleModel->expects($this->once())->method('getBasePath')->willReturn($basePath);
        $this->downloadFileMock->expects($this->once())
            ->method('moveFileFromTmp')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($baseTmpPath, $basePath, $data, $fileName) {
                    if ($arg1 == $baseTmpPath && $arg2 == $basePath && $arg3 == $data['file']) {
                        return $fileName;
                    }
                }
            );
        $this->sampleMock->expects($this->once())->method('setSampleFile')->with($fileName);
        $this->sampleMock->expects($this->once())->method('setSortOrder')->with(1);
        $useDefaultTitle = $data['use_default_title'] ?? false;
        if ($useDefaultTitle) {
            $this->sampleMock->expects($this->once())->method('setTitle')->with(null);
        }
        $this->service->setData($data);

        $this->service->build($this->sampleMock);
    }
}
