<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

use Magento\TestFramework\Helper\ObjectManager;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \Magento\Downloadable\Service\V1\DownloadableLink\ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->objectHelper = new ObjectManager($this);

        $this->repositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->productTypeMock = $this->getMock('\Magento\Downloadable\Model\Product\Type', [], [], '', false);

        $linkBuilder = $this->objectHelper->getObject(
            'Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkInfoBuilder'
        );

        $sampleBuilder = $this->objectHelper->getObject(
            'Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableSampleInfoBuilder'
        );

        $resourceBuilder = $this->objectHelper->getObject(
            '\Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfoBuilder'
        );
        $this->service = $this->objectHelper->getObject(
            '\Magento\Downloadable\Service\V1\DownloadableLink\ReadService',
            [
                'productRepository' => $this->repositoryMock,
                'downloadableType' => $this->productTypeMock,
                'linkBuilder' => $linkBuilder,
                'sampleBuilder' => $sampleBuilder,
                'resourceBuilder' => $resourceBuilder,
            ]
        );

        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
    }

    /**
     * @dataProvider getLinksProvider
     */
    public function testGetLinks($inputData, $inputFileData, $expectationData)
    {
        $productSku = 'downloadable_sku';

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            [
                'getId', 'getStoreTitle', 'getTitle', 'getPrice', 'getNumberOfDownloads',
                'getSortOrder', 'getIsShareable', 'getData', '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->productTypeMock->expects($this->once())
            ->method('getLinks')
            ->with($this->productMock)
            ->will($this->returnValue([$linkMock]));

        $this->setLinkAssertions($linkMock, $inputData, $inputFileData);

        $links = $this->service->getLinks($productSku);
        $this->assertEquals(1, count($links));
        $this->assertEquals($expectationData, reset($links)->__toArray());
    }

    /**
     * @dataProvider getSamplesProvider
     */
    public function testGetSamples($inputData, $inputFileData, $expectationData)
    {
        $productSku = 'downloadable_sku';

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($this->productMock));

        $sampleMock = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            [
                'getId', 'getStoreTitle', 'getTitle',
                'getSortOrder', 'getData', '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->productTypeMock->expects($this->once())
            ->method('getSamples')
            ->with($this->productMock)
            ->will($this->returnValue([$sampleMock]));

        $this->setSampleAssertions($sampleMock, $inputData, $inputFileData);

        $samples = $this->service->getSamples($productSku);
        $this->assertEquals(1, count($samples));
        $this->assertEquals($expectationData, reset($samples)->__toArray());
    }

    protected function setLinkAssertions($resource, $inputData, $fileData)
    {
        $resource->expects($this->once())->method('getId')->will($this->returnValue($inputData['id']));
        $resource->expects($this->once())->method('getStoreTitle')
            ->will($this->returnValue($inputData['store_title']));
        $resource->expects($this->once())->method('getTitle')
            ->will($this->returnValue($inputData['title']));
        $resource->expects($this->any())->method('getPrice')
            ->will($this->returnValue($inputData['price']));
        $resource->expects($this->once())->method('getNumberOfDownloads')
            ->will($this->returnValue($inputData['number_of_downloads']));
        $resource->expects($this->once())->method('getSortOrder')
            ->will($this->returnValue($inputData['sort_order']));
        $resource->expects($this->once())->method('getIsShareable')
            ->will($this->returnValue($inputData['is_shareable']));
        $resource->expects($this->any())->method('getData')->will($this->returnValueMap($fileData));
    }

    protected function setSampleAssertions($resource, $inputData, $fileData)
    {
        $resource->expects($this->once())->method('getId')->will($this->returnValue($inputData['id']));
        $resource->expects($this->once())->method('getStoreTitle')
            ->will($this->returnValue($inputData['store_title']));
        $resource->expects($this->once())->method('getTitle')
            ->will($this->returnValue($inputData['title']));
        $resource->expects($this->once())->method('getSortOrder')
            ->will($this->returnValue($inputData['sort_order']));
        $resource->expects($this->any())->method('getData')->will($this->returnValueMap($fileData));
    }

    public function getLinksProvider()
    {
        $linkData = [
            'id' => 324,
            'store_title' => 'rock melody',
            'title' => 'just melody',
            'price' => 23,
            'number_of_downloads' => 3,
            'sort_order' => 21,
            'is_shareable' => 2,
        ];

        $linkDataGlobalTitle = $linkData;
        $linkDataGlobalTitle['store_title'] = null;
        $linkDataGlobalTitle['title'] = 'global title';

        $linkFileData = [
            ['link_type', null, 'url'],
            ['link_url', null, 'http://link.url'],
            ['link_file', null, ''],
            ['sample_type', null, 'file'],
            ['sample_url', null, ''],
            ['sample_file', null, '/r/o/rock.melody.ogg'],
        ];

        $linkUrl = [
            'type' => 'url',
            'url' => 'http://link.url',
            'file' => '',
        ];

        $sampleFile = [
            'type' => 'file',
            'url' => '',
            'file' => '/r/o/rock.melody.ogg',
        ];

        $linkExpectation = [
            'id' => $linkData['id'],
            'title' => $linkData['store_title'],
            'price' => $linkData['price'],
            'number_of_downloads' => $linkData['number_of_downloads'],
            'sort_order' => $linkData['sort_order'],
            'shareable' => $linkData['is_shareable'],
            'link_resource' => $linkUrl,
            'sample_resource' => $sampleFile,
        ];

        $linkExpectationGlobalTitle = $linkExpectation;
        $linkExpectationGlobalTitle['title'] = 'global title';

        return [
            'linksWithStoreTitle' => [
                $linkData,
                $linkFileData,
                $linkExpectation,
            ],
            'linksWithGlobalTitle' => [
                $linkDataGlobalTitle,
                $linkFileData,
                $linkExpectationGlobalTitle,
            ],
        ];
    }

    public function getSamplesProvider()
    {
        $sampleData = [
            'id' => 324,
            'store_title' => 'rock melody sample',
            'title' => 'just melody sample',
            'sort_order' => 21,
        ];

        $sampleDataGlobalTitle = $sampleData;
        $sampleDataGlobalTitle['store_title'] = null;
        $sampleDataGlobalTitle['title'] = 'sample global title';

        $sampleFileData = [
            ['sample_type', null, 'file'],
            ['sample_url', null, ''],
            ['sample_file', null, '/r/o/rock.melody.ogg'],
        ];

        $sampleFile = [
            'type' => 'file',
            'url' => '',
            'file' => '/r/o/rock.melody.ogg',
        ];

        $sampleExpectation = [
            'id' => $sampleData['id'],
            'title' => $sampleData['store_title'],
            'sort_order' => $sampleData['sort_order'],
            'sample_resource' => $sampleFile,
        ];

        $linkExpectationGlobalTitle = $sampleExpectation;
        $linkExpectationGlobalTitle['title'] = 'sample global title';

        return [
            'samplesWithStoreTitle' => [
                $sampleData,
                $sampleFileData,
                $sampleExpectation,
            ],
            'samplesWithGlobalTitle' => [
                $sampleDataGlobalTitle,
                $sampleFileData,
                $linkExpectationGlobalTitle,
            ],
        ];
    }
}
