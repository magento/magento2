<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\LinkRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentUploaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkDataObjectFactory;

    /**
     * @var LinkRepository
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkHandlerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadataMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock(\Magento\Catalog\Model\ProductRepository::class, [], [], '', false);
        $this->productTypeMock = $this->getMock(\Magento\Downloadable\Model\Product\Type::class, [], [], '', false);
        $this->linkDataObjectFactory = $this->getMockBuilder(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class)
            ->setMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sampleDataObjectFactory = $this->getMockBuilder(
            \Magento\Downloadable\Api\Data\SampleInterfaceFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentValidatorMock = $this->getMock(
            \Magento\Downloadable\Model\Link\ContentValidator::class,
            [],
            [],
            '',
            false
        );
        $this->contentUploaderMock = $this->getMock(
            \Magento\Downloadable\Api\Data\File\ContentUploaderInterface::class
        );
        $this->jsonEncoderMock = $this->getMock(
            \Magento\Framework\Json\EncoderInterface::class
        );
        $this->linkFactoryMock = $this->getMock(
            \Magento\Downloadable\Model\LinkFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
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
        $this->service = new \Magento\Downloadable\Model\LinkRepository(
            $this->repositoryMock,
            $this->productTypeMock,
            $this->linkDataObjectFactory,
            $this->linkFactoryMock,
            $this->contentValidatorMock,
            $this->jsonEncoderMock,
            $this->contentUploaderMock
        );

        $this->entityMetadataMock = $this->getMockBuilder(
            \Magento\Framework\EntityManager\EntityMetadataInterface::class
        )->getMockForAbstractClass();
        $linkRepository = new \ReflectionClass(get_class($this->service));
        $metadataPoolProperty = $linkRepository->getProperty('metadataPool');
        $this->metadataPoolMock = $this->getMockBuilder(
            \Magento\Framework\EntityManager\MetadataPool::class
        )->disableOriginalConstructor()->getMock();
        $metadataPoolProperty->setAccessible(true);
        $metadataPoolProperty->setValue(
            $this->service,
            $this->metadataPoolMock
        );
        $saveHandlerProperty = $linkRepository->getProperty('linkTypeHandler');
        $this->linkHandlerMock = $this->getMockBuilder(
            \Magento\Downloadable\Model\Product\TypeHandler\Link::class
        )->disableOriginalConstructor()->getMock();
        $saveHandlerProperty->setAccessible(true);
        $saveHandlerProperty->setValue(
            $this->service,
            $this->linkHandlerMock
        );

        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->entityMetadataMock);
    }

    /**
     * @param array $linkData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkMock(array $linkData)
    {
        $linkMock = $this->getMock(
            \Magento\Downloadable\Api\Data\LinkInterface::class,
            [],
            [],
            '',
            false
        );

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
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));
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

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link title cannot be empty.
     */
    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
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

        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));

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
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $existingLinkMock = $this->getMock(
            \Magento\Downloadable\Model\Link::class,
            [
                '__wakeup',
                'getId',
                'load',
                'getProductId'
            ],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($existingLinkMock));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));

        $existingLinkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $existingLinkMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());

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
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $existingLinkMock = $this->getMock(
            \Magento\Downloadable\Model\Link::class,
            [
                '__wakeup',
                'getId',
                'load',
                'getProductId'
            ],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($existingLinkMock));
        $linkMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkMock)
            ->will($this->returnValue(true));

        $existingLinkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $existingLinkMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());

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
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'is_shareable' => true,
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue($productId));
        $existingLinkMock = $this->getMock(
            \Magento\Downloadable\Model\Link::class,
            ['__wakeup', 'getId', 'load', 'save', 'getProductId'],
            [],
            '',
            false
        );
        $existingLinkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $existingLinkMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $existingLinkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($existingLinkMock));
        $linkContentMock = $this->getLinkMock($linkData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $this->linkHandlerMock->expects($this->never())->method('save');
        $this->service->save($productSku, $linkContentMock, true);
    }

    public function testDelete()
    {
        $linkId = 1;
        $linkMock = $this->getMock(
            \Magento\Downloadable\Model\Link::class,
            [],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($linkMock));
        $linkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $linkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $linkMock->expects($this->once())->method('delete');

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
            \Magento\Downloadable\Model\Link::class,
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
            \Magento\Downloadable\Model\Link::class,
            [
                'getId',
                'getStoreTitle',
                'getTitle',
                'getPrice',
                'getNumberOfDownloads',
                'getSortOrder',
                'getIsShareable',
                'getData',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $linkInterfaceMock = $this->getMock(\Magento\Downloadable\Api\Data\LinkInterface::class);

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $this->productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->productMock)
            ->will($this->returnValue([$linkMock]));

        $this->setLinkAssertions($linkMock, $linkData);
        $this->linkDataObjectFactory->expects($this->once())->method('create')->willReturn($linkInterfaceMock);

        $this->assertEquals([$linkInterfaceMock], $this->service->getList($productSku));
    }

    protected function setLinkAssertions($resource, $inputData)
    {
        $resource->expects($this->any())->method('getId')->will($this->returnValue($inputData['id']));
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
