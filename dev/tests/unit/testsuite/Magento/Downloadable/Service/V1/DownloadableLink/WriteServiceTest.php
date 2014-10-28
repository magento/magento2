<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var WriteService
     */
    protected $service;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', array(), array(), '', false);
        $this->contentValidatorMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContentValidator',
            array(),
            array(),
            '',
            false
        );
        $this->contentUploaderMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContentUploaderInterface'
        );
        $this->jsonEncoderMock = $this->getMock(
            '\Magento\Framework\Json\EncoderInterface'
        );
        $this->linkFactoryMock = $this->getMock(
            '\Magento\Downloadable\Model\LinkFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('__wakeup', 'getTypeId', 'setDownloadableData', 'save', 'getId', 'getStoreId', 'getStore',
                'getWebsiteIds'),
            array(),
            '',
            false
        );
        $this->service = new WriteService(
            $this->repositoryMock,
            $this->contentValidatorMock,
            $this->contentUploaderMock,
            $this->jsonEncoderMock,
            $this->linkFactoryMock
        );
    }

    /**
     * @param array $linkContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkContentMock(array $linkContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContent',
            array(),
            array(),
            '',
            false
        );

        $contentMock->expects($this->any())->method('getPrice')->will($this->returnValue(
            $linkContentData['price']
        ));
        $contentMock->expects($this->any())->method('getTitle')->will($this->returnValue(
            $linkContentData['title']
        ));
        $contentMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $linkContentData['sort_order']
        ));
        $contentMock->expects($this->any())->method('getNumberOfDownloads')->will($this->returnValue(
            $linkContentData['number_of_downloads']
        ));
        $contentMock->expects($this->any())->method('isShareable')->will($this->returnValue(
            $linkContentData['shareable']
        ));
        if (isset($linkContentData['link_type'])) {
            $contentMock->expects($this->any())->method('getLinkType')->will($this->returnValue(
                $linkContentData['link_type']
            ));
        }
        if (isset($linkContentData['link_url'])) {
            $contentMock->expects($this->any())->method('getLinkUrl')->will($this->returnValue(
                $linkContentData['link_url']
            ));
        }
        return $contentMock;
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $linkContentData = array(
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com/'
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->once())->method('setDownloadableData')->with(array(
            'link' => array(
                array(
                    'link_id' => 0,
                    'is_delete' => 0,
                    'type' => $linkContentData['link_type'],
                    'sort_order' => $linkContentData['sort_order'],
                    'title' => $linkContentData['title'],
                    'price' => $linkContentData['price'],
                    'number_of_downloads' => $linkContentData['number_of_downloads'],
                    'is_shareable' => $linkContentData['shareable'],
                    'link_url' => $linkContentData['link_url'],
                ),
            ),
        ));
        $this->productMock->expects($this->once())->method('save');
        $this->service->create($productSku, $linkContentMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link title cannot be empty.
     */
    public function testCreateThrowsExceptionIfTitleIsEmpty()
    {
        $productSku = 'simple';
        $linkContentData = array(
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'shareable' => true,
            'link_type' => 'url',
            'link_url' => 'http://example.com/'
        );

        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('downloadable'));
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $linkContentMock = $this->getLinkContentMock($linkContentData);
        $this->contentValidatorMock->expects($this->any())->method('isValid')->with($linkContentMock)
            ->will($this->returnValue(true));

        $this->productMock->expects($this->never())->method('save');

        $this->service->create($productSku, $linkContentMock);

    }

    public function testUpdate()
    {
        $websiteId = 1;
        $linkId = 1;
        $productSku = 'simple';
        $productId = 1;
        $linkContentData = array(
            'title' => 'Updated Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $storeMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            array('__wakeup', 'setTitle', 'setPrice', 'setSortOrder', 'setIsShareable', 'setNumberOfDownloads', 'getId',
                'setProductId', 'setStoreId', 'setWebsiteId', 'setProductWebsiteIds', 'load', 'save', 'getProductId'),
            array(),
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

        $this->assertTrue($this->service->update($productSku, $linkId, $linkContentMock));
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
        $linkContentData = array(
            'title' => '',
            'sort_order' => 1,
            'price' => 10.1,
            'number_of_downloads' => 100,
            'shareable' => true,
        );
        $this->repositoryMock->expects($this->any())->method('get')->with($productSku, true)
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            array('__wakeup', 'getId', 'load', 'save', 'getProductId'),
            array(),
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

        $this->service->update($productSku, $linkId, $linkContentMock, true);
    }

    public function testDelete()
    {
        $linkId = 1;
        $linkMock = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            array(),
            array(),
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
            array(),
            array(),
            '',
            false
        );
        $this->linkFactoryMock->expects($this->once())->method('create')->will($this->returnValue($linkMock));
        $linkMock->expects($this->once())->method('load')->with($linkId)->will($this->returnSelf());
        $linkMock->expects($this->once())->method('getId');
        $linkMock->expects($this->never())->method('delete');

        $this->service->delete($linkId);
    }
}
