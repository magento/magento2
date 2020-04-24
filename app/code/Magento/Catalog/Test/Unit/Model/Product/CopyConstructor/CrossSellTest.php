<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CopyConstructor\CrossSell;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CrossSellTest extends TestCase
{
    /**
     * @var CrossSell
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_productMock;

    /**
     * @var MockObject
     */
    protected $_duplicateMock;

    /**
     * @var MockObject
     */
    protected $_linkMock;

    /**
     * @var MockObject
     */
    protected $_linkCollectionMock;

    protected function setUp(): void
    {
        $this->_model = new CrossSell();

        $this->_productMock = $this->createMock(Product::class);

        $this->_duplicateMock = $this->createPartialMock(
            Product::class,
            ['setCrossSellLinkData', '__wakeup']
        );

        $this->_linkMock = $this->createPartialMock(
            Link::class,
            ['__wakeup', 'getAttributes', 'getCrossSellLinkCollection', 'useCrossSellLinks']
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
        $helper = new ObjectManager($this);
        $expectedData = ['100500' => ['some' => 'data']];

        $attributes = ['attributeOne' => ['code' => 'one'], 'attributeTwo' => ['code' => 'two']];

        $this->_linkMock->expects($this->once())->method('useCrossSellLinks');

        $this->_linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $productLinkMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link::class,
            ['__wakeup', 'getLinkedProductId', 'toArray']
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
            Collection::class,
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
