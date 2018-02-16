<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Search\CollectionFilter
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->visibilityMock = $this->getMock('Magento\Catalog\Model\Product\Visibility', [], [], '', false);
        $this->catalogConfigMock = $this->getMock('\Magento\Catalog\Model\Config', [], [], '', false);

        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Layer\Search\CollectionFilter',
            [
                'catalogConfig' => $this->catalogConfigMock,
                'storeManager' => $this->storeManagerMock,
                'productVisibility' => $this->visibilityMock
            ]
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::filter
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::__construct
     */
    public function testFilter()
    {
        $collectionMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection',
            [
                'addAttributeToSelect', 'setStore', 'addMinimalPrice', 'addFinalPrice',
                'addTaxPercents', 'addStoreFilter', 'addUrlRewrite', 'setVisibility'
            ],
            [],
            '',
            false
        );
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);

        $this->catalogConfigMock->expects($this->once())->method('getProductAttributes');
        $this->visibilityMock->expects($this->once())->method('getVisibleInSearchIds');
        $this->storeManagerMock->expects($this->once())->method('getStore');

        $collectionMock->expects($this->once())->method('addAttributeToSelect')
            ->will($this->returnValue($collectionMock));
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
