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

namespace Magento\Catalog\Model\Layer\Search;

class CollectionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibilityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Search\CollectionFilter
     */
    protected $model;

    protected function setUp()
    {
        $this->visibilityMock = $this->getMock('Magento\Catalog\Model\Product\Visibility', array(), array(), '', false);
        $this->catalogConfigMock = $this->getMock('\Magento\Catalog\Model\Config', array(), array(), '', false);
        $this->helperMock = $this->getMock('\Magento\CatalogSearch\Helper\Data', array(), array(), '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');

        $this->model = new CollectionFilter(
            $this->catalogConfigMock, $this->helperMock, $this->storeManagerMock, $this->visibilityMock
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::filter
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::__construct
     */
    public function testFilter()
    {
        $collectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            array(
                'addAttributeToSelect', 'addSearchFilter', 'setStore', 'addMinimalPrice', 'addFinalPrice',
                'addTaxPercents', 'addStoreFilter', 'addUrlRewrite', 'setVisibility'
            ),
            array(),
            '',
            false
        );
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', array(), array(), '', false);
        $queryMock = $this->getMock('Magento\CatalogSearch\Helper\Query', array('getQueryText'), array(), '', false);

        $queryMock->expects($this->once())->method('getQueryText');

        $this->catalogConfigMock->expects($this->once())->method('getProductAttributes');
        $this->visibilityMock->expects($this->once())->method('getVisibleInSearchIds');
        $this->helperMock->expects($this->once())->method('getQuery')->will($this->returnValue($queryMock));
        $this->storeManagerMock->expects($this->once())->method('getStore');

        $collectionMock->expects($this->once())->method('addAttributeToSelect')
            ->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addSearchFilter')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('setStore')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addMinimalPrice')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addFinalPrice')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addTaxPercents')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addStoreFilter')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addUrlRewrite')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('setVisibility')->will($this->returnValue($collectionMock));

        $this->model->filter($collectionMock, $categoryMock);
    }
}
