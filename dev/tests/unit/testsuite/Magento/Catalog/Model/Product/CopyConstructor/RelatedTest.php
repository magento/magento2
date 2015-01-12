<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\CopyConstructor;

class RelatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \\Magento\Catalog\Model\Product\CopyConstructor\Related
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
        $this->_model = new \Magento\Catalog\Model\Product\CopyConstructor\Related();

        $this->_productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $this->_duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['setRelatedLinkData', '__wakeup'],
            [],
            '',
            false
        );

        $this->_linkMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Link',
            ['__wakeup', 'getAttributes', 'getRelatedLinkCollection', 'useRelatedLinks'],
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
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $expectedData = ['100500' => ['some' => 'data']];

        $attributes = ['attributeOne' => ['code' => 'one'], 'attributeTwo' => ['code' => 'two']];

        $this->_linkMock->expects($this->once())->method('useRelatedLinks');

        $this->_linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $productLinkMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Link',
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
            '\Magento\Catalog\Model\Resource\Product\Link\Collection',
            [$productLinkMock]
        );
        $this->_productMock->expects(
            $this->once()
        )->method(
            'getRelatedLinkCollection'
        )->will(
            $this->returnValue($collectionMock)
        );

        $this->_duplicateMock->expects($this->once())->method('setRelatedLinkData')->with($expectedData);

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
