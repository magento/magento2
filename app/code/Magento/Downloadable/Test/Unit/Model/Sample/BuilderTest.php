<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Sample;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Model\Sample\Builder;

/**
 * Class BuilderTest
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $downloadFileMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var Builder
     */
    private $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockComponentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sampleMock;

    protected function setUp()
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

        $this->mockComponentFactory = $this->getMockBuilder('\Magento\Downloadable\Model\SampleFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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
            ->withConsecutive(
                [
                    $baseTmpPath,
                    $basePath,
                    $data['file']
                ]
            )->willReturn($fileName);
        $this->sampleMock->expects($this->once())->method('setSampleFile')->with($fileName);
        $this->sampleMock->expects($this->once())->method('setSortOrder')->with(1);
        $this->service->setData($data);
        
        $this->service->build($this->sampleMock);
    }
}
