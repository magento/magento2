<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\LinkRepository;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sampleDataObjectFactory;

    /**
     * @var LinkRepository
     */
    protected $service;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->productTypeMock = $this->getMock('\Magento\Downloadable\Model\Product\Type', [], [], '', false);
        $this->linkDataObjectFactory = $this->getMockBuilder('\Magento\Downloadable\Api\Data\LinkInterfaceFactory')
            ->setMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sampleDataObjectFactory = $this->getMockBuilder('\Magento\Downloadable\Api\Data\SampleInterfaceFactory')
            ->setMethods(
                [
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentValidatorMock = $this->getMock(
            '\Magento\Downloadable\Model\Link\ContentValidator',
            [],
            [],
            '',
            false
        );
        $this->contentUploaderMock = $this->getMock(
            '\Magento\Downloadable\Api\Data\File\ContentUploaderInterface'
        );
        $this->jsonEncoderMock = $this->getMock(
            '\Magento\Framework\Json\EncoderInterface'
        );
        $this->linkFactoryMock = $this->getMock(
            '\Magento\Downloadable\Model\LinkFactory',
            ['create'],
            [],
            '',
            false
        );
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
                'getWebsiteIds'
            ],
            [],
            '',
            false
        );
        $this->service = new \Magento\Downloadable\Model\LinkRepository(
            $this->repositoryMock,
            $this->productTypeMock,
            $this->linkDataObjectFactory,
            $this->sampleDataObjectFactory,
            $this->linkFactoryMock,
            $this->contentValidatorMock,
            $this->jsonEncoderMock,
            $this->contentUploaderMock
        );
    }

    /**
     * @param array $linkContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkContentMock(array $linkContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Api\Data\LinkContentInterface',
            [],
            [],
            '',
            false
        );

        $contentMock->expects($this->any())->method('getPrice')->will(
            $this->returnValue(
                $linkContentData['price']
            )
        );
        $contentMock->expects($this->any())->method('getTitle')->will(
            $this->returnValue(
                $linkContentData['title']
            )
        );
        $contentMock->expects($this->any())->method('getSortOrder')->will(
            $this->returnValue(
                $linkContentData['sort_order']
            )
        );
        $contentMock->expects($this->any())->method('getNumberOfDownloads')->will(
            $this->returnValue(
                $linkContentData['number_of_downloads']
            )
        );
        $contentMock->expects($this->any())->method('isShareable')->will(
            $this->returnValue(
                $linkContentData['shareable']
            )
        );
        if (isset($linkContentData['link_type'])) {
            $contentMock->expects($this->any())->method('getLinkType')->will(
                $this->returnValue(
                    $linkContentData['link_type']
                )
            );
        }
        if (isset($linkContentData['link_url'])) {
            $contentMock->expects($this->any())->method('getLinkUrl')->will(
                $this->returnValue(
                    $linkContentData['link_url']
                )
            );
        }
        return $contentMock;
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $linkContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->once())->method('setDownloadableData')->with(
            [
                'link' => [
                    [
                        'link_id' => 0,
                        'is_delete' => 0,
                        'type' => $linkContentData['link_type'],
                        'sort_order' => $linkContentData['sort_order'],
                        'title' => $linkContentData['title'],
                        'price' => $linkContentData['price'],
                        'number_of_downloads' => $linkContentData['number_of_downloads'],
                        'is_shareable' => $linkContentData['shareable'],
                        'link_url' => $linkContentData['link_url'],
                    ],
                ],
            ]
        );
        $this->productMock->expects($this->once())->method('save');
        $this->service->save($productSku, $linkContentMock, null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link title cannot be empty.
     */
    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $productSku = 'simple';
        $linkContentData = [
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'shareable' => true,
            'link_type' => 'url',
            'link_url' => 'http://example.com/',
        ];

        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->never())->method('save');

        $this->service->save($productSku, $linkContentMock, null);
    }

    public function testUpdate()
    {
        $websiteId = 1;
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkContentData = [
            'title' => 'Updated Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            [
                '__wakeup',
                'setTitle',
                'setPrice',
                'setSortOrder',
                'setIsShareable',
                'setNumberOfDownloads',
                'getId',
                'setProductId',
                'setStoreId',
                'setWebsiteId',
                'setProductWebsiteIds',
                'load',
                'save',
                'getProductId'
            ],
            [],
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($linkMock));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $linkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $linkMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $linkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setTitle')->with($linkContentData['title'])
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setSortOrder')->with($linkContentData['sort_order'])
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setPrice')->with($linkContentData['price'])
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setIsShareable')->with($linkContentData['shareable'])
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setNumberOfDownloads')->with($linkContentData['number_of_downloads'])
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setProductId')->with($productId)
            ->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setStoreId')->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setWebsiteId')->with($websiteId)->will($this->returnSelf());
        $linkMock->expects($this->once())->method('setProductWebsiteIds')->will($this->returnSelf());
        $linkMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->assertEquals($linkId, $this->service->save($productSku, $linkContentMock, $linkId));
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
        $linkContentData = [
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'shareable' => true,
        ];
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            ['__wakeup', 'getId', 'load', 'save', 'getProductId'],
            [],
            '',
            false
        );
        $linkMock->expects($this->any())->method('getId')->will($this->returnValue($linkId));
        $linkMock->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $linkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($linkMock));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $linkMock->expects($this->never())->method('save');

        $this->service->save($productSku, $linkContentMock, $linkId, true);
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

    public function testGetLinks()
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
            '\Magento\Downloadable\Model\Link',
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
        $this->linkDataObjectFactory->expects($this->once())->method('create')->willReturn($linkInterfaceMock);

        $this->assertEquals([$linkInterfaceMock], $this->service->getLinks($productSku));
    }

    public function testGetSamples()
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

        $this->sampleDataObjectFactory->expects($this->once())->method('create')->willReturn($sampleInterfaceMock);

        $this->assertEquals([$sampleInterfaceMock], $this->service->getSamples($productSku));
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
