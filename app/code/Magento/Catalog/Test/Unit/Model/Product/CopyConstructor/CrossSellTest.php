<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\CopyConstructor;

class CrossSellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\CopyConstructor\CrossSell
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_duplicateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_linkMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_linkCollectionMock;

    protected function setUp()
    {
        $this->_model = new \Magento\Catalog\Model\Product\CopyConstructor\CrossSell();

        $this->_productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $this->_duplicateMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['setCrossSellLinkData', '__wakeup'],
            [],
            '',
            false
        );

        $this->_linkMock = $this->getMock(
            \Magento\Catalog\Model\Product\Link::class,
            ['__wakeup', 'getAttributes', 'getCrossSellLinkCollection', 'useCrossSellLinks'],
            [],
            '',
            false
        );

        $this->_productMock->expects(
            $this->any()
        )->method(
            'getLinkInstance'
        )->will(
            $this->returnValue($this->_linkMock)
        );
    }

    public function testBuild()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $expectedData = ['100500' => ['some' => 'data']];

        $attributes = ['attributeOne' => ['code' => 'one'], 'attributeTwo' => ['code' => 'two']];

        $this->_linkMock->expects($this->once())->method('useCrossSellLinks');

        $this->_linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $productLinkMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link::class,
            ['__wakeup', 'getLinkedProductId', 'toArray'],
            [],
            '',
            false
        );

        $productLinkMock->expects($this->once())->method('getLinkedProductId')->will($this->returnValue('100500'));
        $productLinkMock->expects(
            $this->once()
        )->method(
            'toArray'
        )->with(
            ['one', 'two']
        )->will(
            $this->returnValue(['some' => 'data'])
        );

        $collectionMock = $helper->getCollectionMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Collection::class,
            [$productLinkMock]
        );
        $this->_productMock->expects(
            $this->once()
        )->method(
            'getCrossSellLinkCollection'
        )->will(
            $this->returnValue($collectionMock)
        );

        $this->_duplicateMock->expects($this->once())->method('setCrossSellLinkData')->with($expectedData);

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
