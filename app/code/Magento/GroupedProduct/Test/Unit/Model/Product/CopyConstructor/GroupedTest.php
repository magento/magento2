<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\CopyConstructor\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /**
     * @var Grouped
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
        $this->_model = new Grouped();

        $this->_productMock = $this->createPartialMock(
            Product::class,
            ['getTypeId', '__wakeup', 'getLinkInstance']
        );

        $this->_duplicateMock = $this->createPartialMock(
            Product::class,
            ['setGroupedLinkData', '__wakeup']
        );

        $this->_linkMock = $this->createPartialMock(
            Link::class,
            ['setLinkTypeId', '__wakeup', 'getAttributes', 'getLinkCollection']
        );

        $this->_productMock->expects(
            $this->any()
        )->method(
            'getLinkInstance'
        )->will(
            $this->returnValue($this->_linkMock)
        );
    }

    public function testBuildWithNonGroupedProductType()
    {
        $this->_productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('some value'));

        $this->_duplicateMock->expects($this->never())->method('setGroupedLinkData');

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }

    public function testBuild()
    {
        $helper = new ObjectManager($this);
        $expectedData = ['100500' => ['some' => 'data']];

        $this->_productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );

        $attributes = ['attributeOne' => ['code' => 'one'], 'attributeTwo' => ['code' => 'two']];

        $this->_linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $productLinkMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link::class,
            ['__wakeup', 'getLinkedProductId', 'toArray']
        );
        $this->_linkMock->expects(
            $this->atLeastOnce()
        )->method(
            'setLinkTypeId'
        )->with(
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
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
        $collectionMock->expects($this->once())->method('setProduct')->with($this->_productMock);
        $collectionMock->expects($this->once())->method('addLinkTypeIdFilter');
        $collectionMock->expects($this->once())->method('addProductIdFilter');
        $collectionMock->expects($this->once())->method('joinAttributes');

        $this->_linkMock->expects(
            $this->once()
        )->method(
            'getLinkCollection'
        )->will(
            $this->returnValue($collectionMock)
        );

        $this->_duplicateMock->expects($this->once())->method('setGroupedLinkData')->with($expectedData);

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
