<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\SampleRepository;

class SampleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleRepository
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Model\Entity\MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Sample\ContentValidator
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Helper\File
     */
    protected $contentUploaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Json\Helper\Data
     */
    protected $jsonEncoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Api\Data\SampleInterfaceFactory
     */
    protected $sampleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Product\Type
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\ResourceModel\Sample
     */
    protected $sampleResourceMock;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMock('Magento\Framework\Model\Entity\MetadataPool', [], [], '', false);
        $this->repositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface', [], [], '', false);
        $this->productTypeMock = $this->getMock('\Magento\Downloadable\Model\Product\Type', [], [], '', false);
        $this->sampleResourceMock = $this->getMock(
            'Magento\Downloadable\Model\ResourceModel\Sample',
            [],
            [],
            '',
            false
        );
        $this->contentValidatorMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample\ContentValidator',
            [],
            [],
            '',
            false
        );
        $this->contentUploaderMock = $this->getMock(
            'Magento\Downloadable\Helper\File',
            [],
            [],
            '',
            false
        );
        $this->jsonEncoderMock = $this->getMock(
            'Magento\Framework\Json\Helper\Data',
            [],
            [],
            '',
            false
        );
        $this->sampleFactoryMock = $this->getMockBuilder(
            'Magento\Downloadable\Api\Data\SampleInterfaceFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [
                '__wakeup',
                'getTypeId',
                'setDownloadableData',
                'save',
                'getId',
                'getStoreId',
                'getStore',
                'getWebsiteIds',
                'getData'
            ],
            [],
            '',
            false
        );

        $metadata = $this->getMock('Magento\Framework\Model\Entity\EntityMetadata', [], [], '', false);
        $metadata->expects($this->any())->method('getLinkField')->willReturn('id');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $this->service = new \Magento\Downloadable\Model\SampleRepository(
            $this->metadataPoolMock,
            $this->repositoryMock,
            $this->productTypeMock,
            $this->sampleResourceMock,
            $this->sampleFactoryMock,
            $this->contentValidatorMock,
            $this->jsonEncoderMock,
            $this->contentUploaderMock
        );
    }

    /**
     * @param array $sampleData
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Sample
     */
    protected function getSampleMock(array $sampleData)
    {
        $sampleMock = $this->getMockBuilder(
            'Magento\Downloadable\Model\Sample'
        )->disableOriginalConstructor()
            ->setMethods([
                'setProductId',
                'getId',
                'getPrice',
                'getTitle',
                'getSortOrder',
                'getNumberOfDownloads',
                'getIsShareable',
                'getSampleType',
                'getSampleUrl',
                'getSampleFile',
                'getFile',
                'setSampleFile',
                'setSampleUrl'
            ])->getMock();

        if (isset($sampleData['id'])) {
            $sampleMock->expects($this->any())->method('getId')->willReturn($sampleData['id']);
        }
        $sampleMock->expects($this->any())->method('getTitle')->will($this->returnValue($sampleData['title']));
        $sampleMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $sampleData['sort_order']
        ));

        if (isset($sampleData['sample_type'])) {
            $sampleMock->expects($this->any())->method('getSampleType')->will($this->returnValue(
                $sampleData['sample_type']
            ));
        }
        if (isset($sampleData['sample_url'])) {
            $sampleMock->expects($this->any())->method('getSampleUrl')->will($this->returnValue(
                $sampleData['sample_url']
            ));
        }
        if (isset($sampleData['sample_file'])) {
            $sampleMock->expects($this->any())->method('getSampleFile')->will($this->returnValue(
                $sampleData['sample_file']
            ));
        }
        return $sampleMock;
    }

    public function testSave()
    {
        $sampleId = 1;
        $productSku = 'simple';
        $productId = 1;
        $sampleData = [
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('downloadable');
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with('id')
            ->willReturn($productId);
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($sampleMock)
            ->willReturn(true);
        $sampleMock->expects($this->once())
            ->method('setProductId')
            ->with($productId);

        $this->sampleResourceMock->expects($this->once())
            ->method('save')
            ->with($sampleMock)
            ->willReturn($sampleId);
        $this->assertEquals($sampleId, $this->service->save($productSku, $sampleMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Product type of the product must be 'downloadable'.
     */
    public function testSaveThrowsExceptionIfTypeIdIsWrong()
    {
        $productSku = 'simple';
        $sampleData = [
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('NotDownloadable');
        $sampleMock = $this->getSampleMock($sampleData);

        $this->sampleResourceMock->expects($this->never())
            ->method('save');

        $this->service->save($productSku, $sampleMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Sample title cannot be empty.
     */
    public function testSaveThrowsExceptionIfTitleIsEmpty()
    {
        $productSku = 'simple';
        $sampleData = [
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://example.com/',
        ];

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('downloadable');
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($sampleMock)
            ->willReturn(true);

        $this->sampleResourceMock->expects($this->never())
            ->method('save')
            ->with($sampleMock);

        $this->service->save($productSku, $sampleMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid sample type.
     */
    public function testSaveThrowsExceptionIfSampleTypeIsWrong()
    {
        $productSku = 'simple';
        $sampleData = [
            'title' => '',
            'sort_order' => 1,
            'sample_type' => 'text',
            'sample_url' => 'http://example.com/',
        ];

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('downloadable');
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($sampleMock)
            ->willReturn(true);

        $this->sampleResourceMock->expects($this->never())
            ->method('save')
            ->with($sampleMock);

        $this->service->save($productSku, $sampleMock);
    }

    public function testSaveWithFile()
    {
        $sampleId = 1;
        $productId = 1;
        $productSku = 'simple';
        $sampleFile = '/s/a/sample.jpg';
        $sampleData = [
            'id' => $sampleId,
            'title' => 'Updated Title',
            'sort_order' => 1,
            'sample_type' => 'file',
            'file' => $sampleFile,
        ];
        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('downloadable');
        $this->productMock->expects($this->once())
            ->method('getData')
            ->with('id')
            ->willReturn($productId);
        $sampleMock = $this->getSampleMock($sampleData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($sampleMock)
            ->willReturn(true);
        $sampleMock->expects($this->once())
            ->method('setProductId')
            ->with($productId);

        $sampleMock->expects($this->exactly(2))
            ->method('getFile')
            ->willReturn($sampleFile);
        $this->jsonEncoderMock->expects($this->once())
            ->method('jsonDecode')
            ->willReturn($sampleFile);
        $this->contentUploaderMock->expects($this->once())
            ->method('moveFileFromTmp')
            ->with($sampleMock->getBaseTmpPath(), $sampleMock->getBasePath(), $sampleFile)
            ->willReturn($sampleFile);

        $sampleMock->expects($this->once())
            ->method('setSampleFile')
            ->with($sampleFile);
        $sampleMock->expects($this->once())
            ->method('setSampleUrl')
            ->with(null);

        $this->sampleResourceMock->expects($this->once())
            ->method('save')
            ->with($sampleMock)
            ->willReturn($sampleId);

        $this->assertEquals($sampleId, $this->service->save($productSku, $sampleMock));
    }

    public function testDelete()
    {
        $sampleId = 1;
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            [],
            [],
            '',
            false
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')->willReturn($sampleMock);
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->willReturnSelf();
        $sampleMock->expects($this->any())->method('getId')->willReturn($sampleId);
        $this->sampleResourceMock->expects($this->once())->method('delete')->willReturn(true);

        $this->assertTrue($this->service->delete($sampleId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no downloadable sample with provided ID.
     */
    public function testDeleteThrowsExceptionIfSampleIdIsNotValid()
    {
        $sampleId = 1;
        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            [],
            [],
            '',
            false
        );
        $this->sampleFactoryMock->expects($this->once())->method('create')->willReturn($sampleMock);
        $sampleMock->expects($this->once())->method('load')->with($sampleId)->willReturnSelf();
        $sampleMock->expects($this->any())->method('getId');
        $this->sampleResourceMock->expects($this->never())->method('delete');

        $this->service->delete($sampleId);
    }


    public function testGetList()
    {
        $productSku = 'downloadable_sku';

        $sampleData = [
            'id' => 324,
            'store_title' => 'rock melody sample',
            'title' => 'just melody sample',
            'sort_order' => 21,
            'sample_type' => 'file',
            'sample_url' => null,
            'sample_file' => '/r/o/rock.melody.ogg'
        ];

        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            [
                'getId',
                'getStoreTitle',
                'getTitle',
                'getSampleType',
                'getSampleFile',
                'getSampleUrl',
                'getSortOrder',
                'getData',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $sampleInterfaceMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $this->productTypeMock->expects($this->once())
            ->method('getSamples')
            ->with($this->productMock)
            ->will($this->returnValue([$sampleMock]));

        $this->setSampleAssertions($sampleMock, $sampleData);

        $this->sampleFactoryMock->expects($this->once())->method('create')->willReturn($sampleInterfaceMock);

        $this->assertEquals([$sampleInterfaceMock], $this->service->getList($productSku));
    }

    /**
     * @param $resource
     * @param $inputData
     */
    protected function setSampleAssertions($resource, $inputData)
    {
        $resource->expects($this->any())->method('getId')->will($this->returnValue($inputData['id']));
        $resource->expects($this->any())->method('getStoreTitle')
            ->will($this->returnValue($inputData['store_title']));
        $resource->expects($this->any())->method('getTitle')
            ->will($this->returnValue($inputData['title']));
        $resource->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($inputData['sort_order']));
        $resource->expects($this->any())->method('getSampleType')
            ->will($this->returnValue($inputData['sample_type']));
        $resource->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue($inputData['sample_file']));
        $resource->expects($this->any())->method('getSampleUrl')
            ->will($this->returnValue($inputData['sample_url']));
    }
}
