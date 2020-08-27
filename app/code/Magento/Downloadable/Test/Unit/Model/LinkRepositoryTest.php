<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Downloadable\Api\Data\File\ContentUploaderInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Model\Link\ContentValidator;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\LinkRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Product\TypeHandler\Link;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Json\EncoderInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkRepositoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $repositoryMock;

    /**
     * @var MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var MockObject
     */
    protected $contentUploaderMock;

    /**
     * @var MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $productTypeMock;

    /**
     * @var MockObject
     */
    protected $linkDataObjectFactory;

    /**
     * @var LinkRepository
     */
    protected $service;

    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var MockObject
     */
    protected $linkHandlerMock;

    /**
     * @var MockObject
     */
    protected $entityMetadataMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ProductRepository::class);
        $this->productTypeMock = $this->createMock(Type::class);
        $this->linkDataObjectFactory = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->setMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentValidatorMock = $this->createMock(ContentValidator::class);
        $this->contentUploaderMock = $this->createMock(
            ContentUploaderInterface::class
        );
        $this->jsonEncoderMock = $this->createMock(
            EncoderInterface::class
        );
        $this->linkFactoryMock = $this->createPartialMock(LinkFactory::class, ['create']);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setDownloadableData'])
            ->onlyMethods([
                '__wakeup',
                'getTypeId',
                'save',
                'getId',
                'getStoreId',
                'getStore',
                'getWebsiteIds',
                'getData'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new LinkRepository(
            $this->repositoryMock,
            $this->productTypeMock,
            $this->linkDataObjectFactory,
            $this->linkFactoryMock,
            $this->contentValidatorMock,
            $this->jsonEncoderMock,
            $this->contentUploaderMock
        );

        $this->entityMetadataMock = $this->getMockBuilder(
            EntityMetadataInterface::class
        )->getMockForAbstractClass();
        $linkRepository = new \ReflectionClass(get_class($this->service));
        $metadataPoolProperty = $linkRepository->getProperty('metadataPool');
        $this->metadataPoolMock = $this->getMockBuilder(
            MetadataPool::class
        )->disableOriginalConstructor()
            ->getMock();
        $metadataPoolProperty->setAccessible(true);
        $metadataPoolProperty->setValue(
            $this->service,
            $this->metadataPoolMock
        );
        $saveHandlerProperty = $linkRepository->getProperty('linkTypeHandler');
        $this->linkHandlerMock = $this->getMockBuilder(
            Link::class
        )->disableOriginalConstructor()
            ->getMock();
        $saveHandlerProperty->setAccessible(true);
        $saveHandlerProperty->setValue(
            $this->service,
            $this->linkHandlerMock
        );

        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->entityMetadataMock);
    }

    /**
     * @param array $linkData
     * @return MockObject
     */
    protected function getLinkMock(array $linkData)
    {
        $linkMock = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(
                [
                    'getLinkType',
                    'getId',
                    'getPrice',
                    'getTitle',
                    'getSortOrder',
                    'getNumberOfDownloads',
                    'getIsShareable',
                    'getLinkUrl',
                    'getLinkFile',
                    'hasSampleType',
                ]
            )
            ->getMockForAbstractClass();

        if (isset($linkData['id'])) {
            $linkMock->expects($this->any())->method('getId')->willReturn($linkData['id']);
        }

        $linkMock->expects($this->any())->method('getPrice')->willReturn(
            $linkData['price']
        );
        $linkMock->expects($this->any())->method('getTitle')->willReturn(
            $linkData['title']
        );
        $linkMock->expects($this->any())->method('getSortOrder')->willReturn(
            $linkData['sort_order']
        );
        $linkMock->expects($this->any())->method('getNumberOfDownloads')->willReturn(
            $linkData['number_of_downloads']
        );
        $linkMock->expects($this->any())->method('getIsShareable')->willReturn(
            $linkData['is_shareable']
        );
        if (isset($linkData['link_type'])) {
            $linkMock->expects($this->any())->method('getLinkType')->willReturn(
                $linkData['link_type']
            );
        }
        if (isset($linkData['link_url'])) {
            $linkMock->expects($this->any())->method('getLinkUrl')->willReturn(
                $linkData['link_url']
            );
        }
        if (isset($linkData['link_file'])) {
            $linkMock->expects($this->any())->method('getLinkFile')->willReturn(
                $linkData['link_file']
            );
        }
        return $linkMock;
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $linkData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'is_shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('getTypeId')->willReturn('downloadable');
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->willReturn(true);
        $downloadableData = [
            'link' => [
                [
                    'link_id' => 0,
                    'is_delete' => 0,
                    'type' => $linkData['link_type'],
                    'sort_order' => $linkData['sort_order'],
                    'title' => $linkData['title'],
                    'price' => $linkData['price'],
                    'number_of_downloads' => $linkData['number_of_downloads'],
                    'is_shareable' => $linkData['is_shareable'],
                    'link_url' => $linkData['link_url'],
                ],
            ],
        ];
        $this->linkHandlerMock->expects($this->once())->method('save')
            ->with($this->productMock, $downloadableData);
        $this->service->save($productSku, $linkMock);
    }

    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The link title is empty. Enter the link title and try again.');
        $productSku = 'simple';
        $linkData = [
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];

        $this->productMock->expects($this->any())->method('getTypeId')->willReturn('downloadable');
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->willReturn($this->productMock);
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->willReturn(true);

        $this->productMock->expects($this->never())->method('save');

        $this->service->save($productSku, $linkMock);
    }

    public function testUpdate()
    {
        $websiteId = 1;
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
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('getData')->willReturn($productId);
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $existingLinkMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)->addMethods(['getProductId'])
            ->onlyMethods(['__wakeup', 'getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($existingLinkMock);
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->willReturn(true);

        $existingLinkMock->expects($this->any())->method('getId')->willReturn($linkId);
        $existingLinkMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();

        $this->linkHandlerMock->expects($this->once())->method('save')
            ->with(
                $this->productMock,
                [
                    'link' => [
                        [
                            'link_id' => $linkId,
                            'is_delete' => 0,
                            'type' => $linkData['link_type'],
                            'sort_order' => $linkData['sort_order'],
                            'title' => $linkData['title'],
                            'price' => $linkData['price'],
                            'number_of_downloads' => $linkData['number_of_downloads'],
                            'is_shareable' => $linkData['is_shareable'],
                            'link_url' => $linkData['link_url'],
                        ],
                    ],
                ]
            );

        $this->assertEquals($linkId, $this->service->save($productSku, $linkMock));
    }

    public function testUpdateWithExistingFile()
    {
        $websiteId = 1;
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkFile = '/l/i/link.jpg';
        $encodedFiles = "something";
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
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('getData')->willReturn($productId);
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $existingLinkMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)->addMethods(['getProductId'])
            ->onlyMethods(['__wakeup', 'getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($existingLinkMock);
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->willReturn(true);

        $existingLinkMock->expects($this->any())->method('getId')->willReturn($linkId);
        $existingLinkMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with(
                [
                    [
                        'file' => $linkFile,
                        'status' => 'old'
                    ]
                ]
            )->willReturn($encodedFiles);

        $this->linkHandlerMock->expects($this->once())->method('save')
            ->with(
                $this->productMock,
                [
                    'link' => [
                        [
                            'link_id' => $linkId,
                            'is_delete' => 0,
                            'type' => $linkData['link_type'],
                            'sort_order' => $linkData['sort_order'],
                            'title' => $linkData['title'],
                            'price' => $linkData['price'],
                            'number_of_downloads' => $linkData['number_of_downloads'],
                            'is_shareable' => $linkData['is_shareable'],
                            'file' => $encodedFiles,
                        ],
                    ],
                ]
            );

        $this->assertEquals($linkId, $this->service->save($productSku, $linkMock));
    }

    public function testUpdateThrowsExceptionIfTitleIsEmptyAndScopeIsGlobal()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The link title is empty. Enter the link title and try again.');
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkData = [
            'id' => $linkId,
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
            'link_type' => 'url',
            'link_url' => 'https://google.com',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('getData')->willReturn($productId);
        $existingLinkMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)->addMethods(['getProductId'])
            ->onlyMethods(['__wakeup', 'getId', 'load', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $existingLinkMock->expects($this->any())->method('getId')->willReturn($linkId);
        $existingLinkMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($existingLinkMock);
        $linkContentMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->willReturn(true);

        $this->linkHandlerMock->expects($this->never())->method('save');
        $this->service->save($productSku, $linkContentMock, true);
    }

    public function testDelete()
    {
        $linkId = 1;
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkMock);
        $linkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();
        $linkMock->expects($this->any())->method('getId')->willReturn($linkId);
        $linkMock->expects($this->once())->method('delete');

        $this->assertTrue($this->service->delete($linkId));
    }

    public function testDeleteThrowsExceptionIfLinkIdIsNotValid()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'No downloadable link with the provided ID was found. Verify the ID and try again.'
        );
        $linkId = 1;
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkMock);
        $linkMock->expects($this->once())->method('load')->with($linkId)->willReturnSelf();
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
            'link_file' => null,
        ];

        $linkMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)->addMethods(['getStoreTitle'])
            ->onlyMethods([
                'getId',
                'getTitle',
                'getPrice',
                'getNumberOfDownloads',
                'getSortOrder',
                'getIsShareable',
                'getData',
                '__wakeup',
                'getSampleType',
                'getSampleFile',
                'getSampleUrl',
                'getLinkType',
                'getLinkFile',
                'getLinkUrl'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $linkInterfaceMock = $this->getMockForAbstractClass(LinkInterface::class);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $this->productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->productMock)
            ->willReturn([$linkMock]);

        $this->setLinkAssertions($linkMock, $linkData);
        $this->linkDataObjectFactory->expects($this->once())->method('create')->willReturn($linkInterfaceMock);

        $this->assertEquals([$linkInterfaceMock], $this->service->getList($productSku));
    }

    /**
     * @param $resource
     * @param $inputData
     */
    protected function setLinkAssertions($resource, $inputData)
    {
        $resource->expects($this->any())->method('getId')->willReturn($inputData['id']);
        $resource->expects($this->any())->method('getStoreTitle')
            ->willReturn($inputData['store_title']);
        $resource->expects($this->any())->method('getTitle')
            ->willReturn($inputData['title']);
        $resource->expects($this->any())->method('getSampleType')
            ->willReturn($inputData['sample_type']);
        $resource->expects($this->any())->method('getSampleFile')
            ->willReturn($inputData['sample_file']);
        $resource->expects($this->any())->method('getSampleUrl')
            ->willReturn($inputData['sample_url']);
        $resource->expects($this->any())->method('getPrice')
            ->willReturn($inputData['price']);
        $resource->expects($this->once())->method('getNumberOfDownloads')
            ->willReturn($inputData['number_of_downloads']);
        $resource->expects($this->once())->method('getSortOrder')
            ->willReturn($inputData['sort_order']);
        $resource->expects($this->once())->method('getIsShareable')
            ->willReturn($inputData['is_shareable']);
        $resource->expects($this->any())->method('getLinkType')
            ->willReturn($inputData['link_type']);
        $resource->expects($this->any())->method('getlinkFile')
            ->willReturn($inputData['link_file']);
        $resource->expects($this->any())->method('getLinkUrl')
            ->willReturn($inputData['link_url']);
    }
}
