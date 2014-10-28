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

class SamplesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples
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
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $downloadableSampleModel;

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
                'getSamples'
            ),
            array(),
            '',
            false
        );
        $this->downloadableSampleModel = $this->getMock(
            '\Magento\Downloadable\Model\Sample',
            array(
                '__wakeup',
                'getId',
                'getTitle',
                'getSampleFile',
                'getSampleType',
                'getSortOrder'
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
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples',
            array(
                'urlBuilder' => $this->urlBuilder,
                'urlFactory' => $urlFactory,
                'coreRegistry' => $this->coreRegistry,
                'escaper' => $this->escaper,
                'downloadableFile' => $this->fileHelper)
        );
    }

    /**
     * Test that getConfig method retrieve \Magento\Framework\Object object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf('Magento\Framework\Object', $this->block->getConfig());
    }

    public function testGetSampleData()
    {
        $expectingFileData = array(
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
        $this->downloadableProductModel->expects($this->any())->method('getSamples')
            ->will($this->returnValue(array($this->downloadableSampleModel)));
        $this->coreRegistry->expects($this->any())->method('registry')
            ->will($this->returnValue($this->productModel));
        $this->downloadableSampleModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->downloadableSampleModel->expects($this->any())->method('getTitle')
            ->will($this->returnValue('Sample Title'));
        $this->downloadableSampleModel->expects($this->any())->method('getSampleUrl')
            ->will($this->returnValue(null));
        $this->downloadableSampleModel->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue('file/sample.gif'));
        $this->downloadableSampleModel->expects($this->any())->method('getSampleType')
            ->will($this->returnValue('file'));
        $this->downloadableSampleModel->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue(0));
        $this->escaper->expects($this->any())->method('escapeHtml')
            ->will($this->returnValue('Sample Title'));
        $this->fileHelper->expects($this->any())->method('getFilePath')
            ->will($this->returnValue('/file/path/sample.gif'));
        $this->fileHelper->expects($this->any())->method('ensureFileInFilesystem')
            ->will($this->returnValue(true));
        $this->fileHelper->expects($this->any())->method('getFileSize')
            ->will($this->returnValue('1.1'));
        $this->urlBuilder->expects($this->any())->method('getUrl')
            ->will($this->returnValue('final_url'));
        $sampleData = $this->block->getSampleData();
        foreach ($sampleData as $sample) {
            $fileSave = $sample->getFileSave(0);
            $this->assertEquals($expectingFileData['sample_file'], $fileSave);
        }
    }
}
