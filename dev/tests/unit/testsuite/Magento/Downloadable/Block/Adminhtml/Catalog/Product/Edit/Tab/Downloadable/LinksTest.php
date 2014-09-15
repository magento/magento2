<?php
/**
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
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $downloadableProductModel;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $downloadableLinkModel;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBuilder;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->urlBuilder = $this->getMock('Magento\Backend\Model\Url', array('getUrl'), array(), '', false);
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', array(), array(), '', false);
        $urlFactory = $this->getMock('Magento\Backend\Model\UrlFactory', array(), array(), '', false);
        $this->fileHelper = $this->getMock(
            '\Magento\Downloadable\Helper\File',
            array(
                'getFilePath',
                'ensureFileInFilesystem',
                'getFileSize'
            ),
            array(),
            '',
            false
        );
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(
                '__wakeup',
                'getTypeId',
                'getTypeInstance',
                'getStoreId'
            ),
            array(),
            '',
            false
        );
        $this->downloadableProductModel = $this->getMock(
            '\Magento\Downloadable\Model\Product\Type',
            array(
                '__wakeup',
                'getLinks'
            ),
            array(),
            '',
            false
        );
        $this->downloadableLinkModel = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            array(
                '__wakeup',
                'getId',
                'getTitle',
                'getPrice',
                'getNumberOfDownloads',
                'getLinkUrl',
                'getLinkType',
                'getSampleFile',
                'getSampleType',
                'getSortOrder',
                'getLinkFile',
                'getStoreTitle'
            ),
            array(),
            '',
            false
        );

        $this->coreRegistry = $this->getMock(
            '\Magento\Framework\Registry',
            array(
                '__wakeup',
                'registry'
            ),
            array(),
            '',
            false
        );

        $this->escaper = $this->getMock('\Magento\Framework\Escaper', array('escapeHtml'), array(), '', false);

        $this->block = $objectManagerHelper->getObject(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links',
            array(
                'urlBuilder' => $this->urlBuilder,
                'attributeFactory' => $attributeFactory,
                'urlFactory' => $urlFactory,
                'coreRegistry' => $this->coreRegistry,
                'escaper' => $this->escaper,
                'downloadableFile' => $this->fileHelper
            )
        );
    }

    /**
     * Test that getConfig method retrieve \Magento\Framework\Object object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf('Magento\Framework\Object', $this->block->getConfig());
    }

    public function testGetLinkData()
    {
        $expectingFileData = array(
            'file' => array(
                'file' => 'file/link.gif',
                'name' => '<a href="final_url">link.gif</a>',
                'size' => '1.1',
                'status' => 'old'
            ),
            'sample_file' => array(
                'file' => 'file/sample.gif',
                'name' => '<a href="final_url">sample.gif</a>',
                'size' => '1.1',
                'status' => 'old'
            )
        );

        $this->productModel->expects($this->any())->method('getTypeId')
            ->will($this->returnValue('downloadable'));
        $this->productModel->expects($this->any())->method('getTypeInstance')
            ->will($this->returnValue($this->downloadableProductModel));
        $this->productModel->expects($this->any())->method('getStoreId')
            ->will($this->returnValue(0));
        $this->downloadableProductModel->expects($this->any())->method('getLinks')
            ->will($this->returnValue(array($this->downloadableLinkModel)));
        $this->coreRegistry->expects($this->any())->method('registry')
            ->will($this->returnValue($this->productModel));
        $this->downloadableLinkModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->downloadableLinkModel->expects($this->any())->method('getTitle')
            ->will($this->returnValue('Link Title'));
        $this->downloadableLinkModel->expects($this->any())->method('getPrice')
            ->will($this->returnValue('10'));
        $this->downloadableLinkModel->expects($this->any())->method('getNumberOfDownloads')
            ->will($this->returnValue('6'));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkUrl')
            ->will($this->returnValue(null));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkType')
            ->will($this->returnValue('file'));
        $this->downloadableLinkModel->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue('file/sample.gif'));
        $this->downloadableLinkModel->expects($this->any())->method('getSampleType')
            ->will($this->returnValue('file'));
        $this->downloadableLinkModel->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue(0));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkFile')
            ->will($this->returnValue('file/link.gif'));
        $this->downloadableLinkModel->expects($this->any())->method('getStoreTitle')
            ->will($this->returnValue('Store Title'));
        $this->escaper->expects($this->any())->method('escapeHtml')
            ->will($this->returnValue('Link Title'));
        $this->fileHelper->expects($this->any())->method('getFilePath')
            ->will($this->returnValue('/file/path/link.gif'));
        $this->fileHelper->expects($this->any())->method('ensureFileInFilesystem')
            ->will($this->returnValue(true));
        $this->fileHelper->expects($this->any())->method('getFileSize')
            ->will($this->returnValue('1.1'));
        $this->urlBuilder->expects($this->any())->method('getUrl')
            ->will($this->returnValue('final_url'));
        $linkData = $this->block->getLinkData();
        foreach ($linkData as $link) {
            $fileSave = $link->getFileSave(0);
            $sampleFileSave = $link->getSampleFileSave(0);
            $this->assertEquals($expectingFileData['file'], $fileSave);
            $this->assertEquals($expectingFileData['sample_file'], $sampleFileSave);

        }
    }
}
