<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\LinkRepository;
use Magento\Framework\DataObject;

class LinkRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Model\Entity\MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Link\ContentValidator
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Api\Data\LinkInterfaceFactory
     */
    protected $linkFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\Product\Type
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\ResourceModel\Link
     */
    protected $linkResourceMock;

    /**
     * @var LinkRepository
     */
    protected $service;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMock('Magento\Framework\Model\Entity\MetadataPool', [], [], '', false);
        $this->repositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface', [], [], '', false);
        $this->productTypeMock = $this->getMock('\Magento\Downloadable\Model\Product\Type', [], [], '', false);
        $this->linkResourceMock = $this->getMock('Magento\Downloadable\Model\ResourceModel\Link', [], [], '', false);
        $this->contentValidatorMock = $this->getMock(
            '\Magento\Downloadable\Model\Link\ContentValidator',
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
        $this->linkFactoryMock = $this->getMockBuilder(
            'Magento\Downloadable\Api\Data\LinkInterfaceFactory'
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
        $this->service = new \Magento\Downloadable\Model\LinkRepository(
            $this->metadataPoolMock,
            $this->repositoryMock,
            $this->productTypeMock,
            $this->linkResourceMock,
            $this->linkFactoryMock,
            $this->contentValidatorMock,
            $this->jsonEncoderMock,
            $this->contentUploaderMock
        );
    }

    /**
     * @param array $linkData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkMock(array $linkData)
    {
        $linkMock = $this->getMockBuilder(
            'Magento\Downloadable\Model\Link'
        )->disableOriginalConstructor()
            ->setMethods([
                'setProductId',
                'getId',
                'getPrice',
                'getTitle',
                'getSortOrder',
                'getNumberOfDownloads',
                'getIsShareable',
                'getLinkType',
                'getLinkUrl',
                'getLinkFile',
            ])->getMock();

        if (isset($linkData['id'])) {
            $linkMock->expects($this->any())->method('getId')->willReturn($linkData['id']);
        }

        $linkMock->expects($this->any())->method('getPrice')->will(
            $this->returnValue(
                $linkData['price']
            )
        );
        $linkMock->expects($this->any())->method('getTitle')->will(
            $this->returnValue(
                $linkData['title']
            )
        );
        $linkMock->expects($this->any())->method('getSortOrder')->will(
            $this->returnValue(
                $linkData['sort_order']
            )
        );
        $linkMock->expects($this->any())->method('getNumberOfDownloads')->will(
            $this->returnValue(
                $linkData['number_of_downloads']
            )
        );
        $linkMock->expects($this->any())->method('getIsShareable')->will(
            $this->returnValue(
                $linkData['is_shareable']
            )
        );
        if (isset($linkData['link_type'])) {
            $linkMock->expects($this->any())->method('getLinkType')->will(
                $this->returnValue(
                    $linkData['link_type']
                )
            );
        }
        if (isset($linkData['link_url'])) {
            $linkMock->expects($this->any())->method('getLinkUrl')->will(
                $this->returnValue(
                    $linkData['link_url']
                )
            );
        }
        if (isset($linkData['link_file'])) {
            $linkMock->expects($this->any())->method('getLinkFile')->will(
                $this->returnValue(
                    $linkData['link_file']
                )
            );
        }
        return $linkMock;
    }

    public function testCreate()
    {
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'is_shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($productId);
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($linkMock)
            ->willReturn(true);
        $linkMock->expects($this->once())
            ->method('setProductId')
            ->with($productId);
        $this->linkResourceMock->expects($this->once())
            ->method('save')
            ->with($linkMock);
        $linkMock->expects($this->once())->method('getId')->willReturn($linkId);
        $this->assertEquals($linkId, $this->service->save($productSku, $linkMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link title cannot be empty.
     */
    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];

        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')
            ->with('id')
            ->will($this->returnValue($productId));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));
        $linkMock->expects($this->never())->method('setProductId')->with($productId);
        $this->linkResourceMock->expects($this->never())
            ->method('save')
            ->with($linkMock);
        $this->service->save($productSku, $linkMock);
    }

    public function testUpdate()
    {
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'id' => $linkId,
            'title' => 'Updated Title',
            'sort_order' => 1,
            'price' => 10.1,
            'is_shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')
            ->with('id')
            ->will($this->returnValue($productId));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));
        $linkMock->expects($this->once())->method('setProductId')->with($productId);
        $this->linkResourceMock->expects($this->once())
            ->method('save')
            ->with($linkMock)
            ->willReturn($linkId);
        $this->assertEquals($linkId, $this->service->save($productSku, $linkMock));
    }

    public function testUpdateWithExistingFile()
    {
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkFile = '/l/i/link.jpg';
        $linkData = [
            'id' => $linkId,
            'title' => 'Updated Title',
            'sort_order' => 1,
            'price' => 10.1,
            'is_shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'file',
            'link_file' => $linkFile,
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')
            ->with('id')
            ->will($this->returnValue($productId));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));

        $linkMock->expects($this->once())->method('setProductId')->with($productId);
        $this->linkResourceMock->expects($this->once())
            ->method('save')
            ->with($linkMock)
            ->willReturn($linkId);
        $this->assertEquals($linkId, $this->service->save($productSku, $linkMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link title cannot be empty.
     */
    public function testUpdateThrowsExceptionIfTitleIsEmptyAndScopeIsGlobal()
    {
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'id' => $linkId,
            'title' => '',
            'link_type' => 'url',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
        ];
        $this->repositoryMock->expects($this->any())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($productId);
        $linkContentMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($linkContentMock)
            ->willReturn(true);

        $this->service->save($productSku, $linkContentMock, true);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid link type.
     */
    public function testUpdateThrowsExceptionIfLinkTypeIsEmpty()
    {
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'id' => $linkId,
            'title' => 'Title',
            'link_type' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
        ];
        $this->repositoryMock->expects($this->any())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($productId);
        $linkContentMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($linkContentMock)
            ->willReturn(true);

        $this->service->save($productSku, $linkContentMock, true);
    }

    public function testDelete()
    {
        $linkId = 1;
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            [],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkMock);
        $linkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();
        $linkMock->expects($this->any())->method('getId')->willReturn($linkId);
        $this->linkResourceMock->expects($this->once())->method('delete')->willReturn(true);

        $this->assertTrue($this->service->delete($linkId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no downloadable link with provided ID.
     */
    public function testDeleteThrowsExceptionIfLinkIdIsNotValid()
    {
        $linkId = 1;
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            [],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($linkMock));
        $linkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $linkMock->expects($this->once())->method('getId');
        $linkMock->expects($this->never())->method('delete');

        $this->service->delete($linkId);
    }

    public function testGetList()
    {
        $productSku = 'downloadable_sku';

        $linkData = [
            'id' => 324,
            'store_title' => 'rock melody',
            'title' => 'just melody',
            'price' => 23,
            'number_of_downloads' => 3,
            'sort_order' => 21,
            'is_shareable' => 2,
            'sample_type' => 'file',
            'sample_url' => null,
            'sample_file' => '/r/o/rock.melody.ogg',
            'link_type' => 'url',
            'link_url' => 'http://link.url',
            'link_file' => null
        ];

        $linkMock = $this->getMock(
            'Magento\Downloadable\Model\Link',
            [
                'getId',
                'getStoreTitle',
                'getTitle',
                'getPrice',
                'getNumberOfDownloads',
                'getSortOrder',
                'getIsShareable',
                'getData',
                '__wakeup',
                'setId'
            ],
            [],
            '',
            false
        );
        $linkInterfaceMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $this->productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->productMock)
            ->will($this->returnValue([$linkMock]));

        $this->setLinkAssertions($linkMock, $linkData);
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkInterfaceMock);

        $this->assertEquals([$linkInterfaceMock], $this->service->getList($productSku));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $resource
     * @param \PHPUnit_Framework_MockObject_MockObject $linkInterfaceMock
     * @param array $inputData
     */
    protected function setLinkAssertions($resource, $inputData)
    {
        $resource->expects($this->once())->method('getId')->willReturn($inputData['id']);
        $resource->expects($this->any())->method('getStoreTitle')
            ->will($this->returnValue($inputData['store_title']));
        $resource->expects($this->any())->method('getTitle')
            ->will($this->returnValue($inputData['title']));
        $resource->expects($this->any())->method('getSampleType')
            ->will($this->returnValue($inputData['sample_type']));
        $resource->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue($inputData['sample_file']));
        $resource->expects($this->any())->method('getSampleUrl')
            ->will($this->returnValue($inputData['sample_url']));
        $resource->expects($this->any())->method('getPrice')
            ->will($this->returnValue($inputData['price']));
        $resource->expects($this->once())->method('getNumberOfDownloads')
            ->will($this->returnValue($inputData['number_of_downloads']));
        $resource->expects($this->once())->method('getSortOrder')
            ->will($this->returnValue($inputData['sort_order']));
        $resource->expects($this->once())->method('getIsShareable')
            ->will($this->returnValue($inputData['is_shareable']));
        $resource->expects($this->any())->method('getLinkType')
            ->will($this->returnValue($inputData['link_type']));
        $resource->expects($this->any())->method('getlinkFile')
            ->will($this->returnValue($inputData['link_file']));
        $resource->expects($this->any())->method('getLinkUrl')
            ->will($this->returnValue($inputData['link_url']));
    }
}
