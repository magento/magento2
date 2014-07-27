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
namespace Magento\Catalog\Model;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Url
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_categoryHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rewriteModel;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_resourceModel = $this->getMock(
            '\Magento\Catalog\Model\Resource\Url',
            array(
                '__wakeup',
                'getStores',
                'clearStoreInvalidRewrites',
                'getProductsByStore',
                'prepareRewrites',
                'getCategories',
                'getCategory',
                'getCategoryModel',
                'loadCategoryChilds',
                'checkRequestPaths',
                'saveRewrite',
                'clearCategoryProduct',
                'getCategoryParentPath',
                'findFinalTargetPath',
                'deleteRewriteRecord',
                'saveCategoryAttribute',
                'getProductsByCategory',
                'deleteCategoryProductStoreRewrites'
            ),
            array(),
            '',
            false
        );
        $this->_urlFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\UrlFactory',
            array(
                'create',
                'formatUrlKey',
                'getUrlPath'
            )
        );
        $this->_storeModel = $this->getMock(
            '\Magento\Store\Model\Store',
            array(
                '__wakeup',
                'getId',
                'getRootCategoryId'
            ),
            array(),
            '',
            false
        );
        $this->_productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(
                '__wakeup',
                'getCategoryIds',
                'getId',
                'getResource',
                'getUrlPath'
            ),
            array(),
            '',
            false
        );
        $this->_categoryModel = $this->getMock(
            'Magento\Catalog\Model\Category',
            array(
                '__wakeup',
                'getId',
                'getStoreId',
                'getChilds',
                'getAllChilds',
                'formatUrlKey',
                'getUrlKey',
                'getCategoryUrlPath',
                'getName'
            ),
            array(),
            '',
            false
        );
        $this->_categoryFactory = $this->getMock('\Magento\Catalog\Model\CategoryFactory');
        $this->_categoryHelper = $this->getMock(
            'Magento\Catalog\Helper\Category',
            array(
                'getCategoryUrlPath',
                'getCategoryUrlSuffix'
            ),
            array(),
            '',
            false
        );
        $this->_rewriteModel = $this->getMock(
            'Magento\UrlRewrite\Model\UrlRewrite',
            array(
                '__wakeup',
                'getRequestPath'
            ),
            array(),
            '',
            false
        );

        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Url',
            array(
                'urlFactory' => $this->_urlFactory,
                'catalogCategoryFactory' => $this->_categoryFactory,
                'catalogCategory' => $this->_categoryHelper
            )
        );

        $this->_urlFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->_resourceModel));
        $this->_resourceModel->expects($this->any())->method('getCategory')
            ->will($this->returnValue($this->_categoryModel));
        $this->_resourceModel->expects($this->any())->method('loadCategoryChilds')
            ->will($this->returnValue($this->_categoryModel));
        $this->_resourceModel->expects($this->any())->method('saveRewrite')
            ->will($this->returnSelf());
        $this->_categoryModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->_categoryModel->expects($this->any())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->_categoryModel->expects($this->any())->method('getChilds')
            ->will($this->returnSelf());
        $this->_storeModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
    }

    public function testGenerateUniqueIdPath()
    {
        $path = $this->_model->generateUniqueIdPath();
        $this->assertNotContains('.', $path);
        $this->assertContains('_', $path);
        $this->assertNotEquals($path, $this->_model->generateUniqueIdPath());
    }

    public function testRefreshRewrites()
    {
        $rewrite = array('category/1' => $this->_rewriteModel);
        $validatedPath = 'validated_path.html';

        $this->_urlFactory->expects($this->any())->method('formatUrlKey')
            ->will($this->returnValue('url_formatted'));
        $this->_urlFactory->expects($this->any())->method('getUrlPath')
            ->will($this->returnValue($validatedPath));
        $this->_resourceModel->expects($this->any())->method('prepareRewrites')
            ->will($this->returnValue($rewrite));
        $this->_resourceModel->expects($this->at(0))->method('getStores')
            ->will($this->returnValue(array($this->_storeModel)));
        $this->_resourceModel->expects($this->any())->method('getStores')
            ->will($this->returnValue($this->_storeModel));
        $this->_resourceModel->expects($this->once())->method('clearStoreInvalidRewrites')
            ->will($this->returnSelf());
        $this->_resourceModel->expects($this->at(14))->method('getProductsByStore')
            ->will($this->returnValue(null));
        $this->_resourceModel->expects($this->any())->method('getProductsByStore')
            ->will($this->returnValue(array($this->_productModel)));
        $this->_resourceModel->expects($this->any())->method('getCategories')
            ->will($this->returnValue(array($this->_categoryModel)));
        $this->_resourceModel->expects($this->once())->method('checkRequestPaths')
            ->will($this->returnValue($validatedPath));
        $this->_resourceModel->expects($this->once())->method('clearCategoryProduct')
            ->will($this->returnSelf());
        $this->_productModel->expects($this->any())->method('getCategoryIds')
            ->will($this->returnValue(array(1)));
        $this->_productModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->_productModel->expects($this->any())->method('getResource')
            ->will($this->returnValue($this->_resourceModel));
        $this->_productModel->expects($this->once())->method('getUrlPath')
            ->will($this->returnValue($validatedPath));
        $this->_storeModel->expects($this->any())->method('getRootCategoryId')
            ->will($this->returnValue(1));

        $this->_model->refreshRewrites();
    }

    /**
     * @param string $targetPathExecute
     * @param bool $changeRequestPath
     *
     * @dataProvider refreshcategoryRewriteDataProvider
     */
    public function testRefreshCategoryRewrite($targetPathExecute, $changeRequestPath)
    {
        $categoryId = 1;
        $rewrite = array('category/1' => $this->_rewriteModel);

        $this->_resourceModel->expects($this->once())->method('prepareRewrites')
            ->will($this->returnValue($rewrite));
        $this->_resourceModel->expects($this->at(0))->method('getStores')
            ->will($this->returnValue(array($this->_storeModel)));
        $this->_resourceModel->expects($this->once())->method('getStores')
            ->will($this->returnValue($this->_storeModel));
        $this->_resourceModel->expects($this->any())->method('getCategoryModel')
            ->will($this->returnValue($this->_categoryModel));
        $this->_resourceModel->expects($this->once())->method('getCategoryParentPath')
            ->will($this->returnValue('parent_path'));
        $this->_resourceModel->expects($this->any())->method('deleteRewriteRecord')
            ->will($this->returnSelf());
        $this->_resourceModel->expects($this->any())->method('saveCategoryAttribute')
            ->will($this->returnSelf());
        $this->_resourceModel->expects($this->any())->method('getProductsByCategory')
            ->will($this->returnValue(null));
        $this->_resourceModel->expects($this->any())->method('deleteCategoryProductStoreRewrites')
            ->will($this->returnSelf());
        $this->_resourceModel->expects($this->$targetPathExecute())->method('findFinalTargetPath')
            ->will($this->returnValue('category/1'));
        $this->_categoryModel->expects($this->any())->method('getAllChilds')
            ->will($this->returnValue(array($this->_categoryModel)));
        $this->_categoryModel->expects($this->any())->method('formatUrlKey')
            ->will($this->returnValue('url_formatted'));
        $this->_categoryModel->expects($this->any())->method('getUrlKey')
            ->will($this->returnValue('url_key'));
        $this->_categoryModel->expects($this->any())->method('getCategoryUrlPath')
            ->will($this->returnValue('category_parent_path'));
        $this->_categoryHelper->expects($this->once())->method('getCategoryUrlPath')
            ->will($this->returnValue('category_parent_path'));
        $this->_categoryHelper->expects($this->once())->method('getCategoryUrlSuffix')
            ->will($this->returnValue('suffics'));
        $this->_rewriteModel->expects($this->once())->method('getRequestPath')
            ->will($this->returnValue('category_parent_pathurl_formatted-1suffics'));

        $this->_model->refreshCategoryRewrite($categoryId, '', true, $changeRequestPath);
    }

    public function refreshcategoryRewriteDataProvider()
    {
        return array(
            array('once', true),
            array('never', false)
        );
    }
}
