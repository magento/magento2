<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

class CopierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Copier
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $copyConstructorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->copyConstructorMock = $this->getMock('\Magento\Catalog\Model\Product\CopyConstructorInterface');
        $this->productFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productMock->expects($this->any())->method('getId')->will($this->returnValue('1'));
        $this->productMock->expects($this->any())->method('getData')->will($this->returnValue('product data'));

        $this->_model = new Copier($this->copyConstructorMock, $this->productFactoryMock);
    }

    public function testCopy()
    {
        $this->productMock->expects($this->atLeastOnce())->method('getWebsiteIds');
        $this->productMock->expects($this->atLeastOnce())->method('getCategoryIds');

        $resourceMock = $this->getMock('\Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $optionMock = $this->getMock('\Magento\Catalog\Model\Product\Option', [], [], '', false);
        $this->productMock->expects($this->once())->method('getResource')->will($this->returnValue($resourceMock));
        $this->productMock->expects($this->once())->method('getOptionInstance')->will($this->returnValue($optionMock));

        $duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [
                '__wakeup',
                'setData',
                'setIsDuplicate',
                'setOriginalId',
                'setStatus',
                'setCreatedAt',
                'setUpdatedAt',
                'setId',
                'setStoreId',
                'getId',
                'save',
                'setUrlKey',
                'getUrlKey',
            ],
            [],
            '',
            false
        );
        $this->productFactoryMock->expects($this->once())->method('create')->will($this->returnValue($duplicateMock));

        $duplicateMock->expects($this->once())->method('setIsDuplicate')->with(true);
        $duplicateMock->expects($this->once())->method('setOriginalId')->with(1);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        );
        $duplicateMock->expects($this->once())->method('setCreatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setUpdatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setId')->with(null);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStoreId'
        )->with(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        $duplicateMock->expects($this->once())->method('setData')->with('product data');
        $this->copyConstructorMock->expects($this->once())->method('build')->with($this->productMock, $duplicateMock);
        $duplicateMock->expects($this->once())->method('getUrlKey')->willReturn('urk-key-1');
        $duplicateMock->expects($this->once())->method('setUrlKey')->with('urk-key-2');
        $duplicateMock->expects($this->once())->method('save');
        $duplicateMock->expects($this->any())->method('getId')->will($this->returnValue(2));
        $optionMock->expects($this->once())->method('duplicate')->with(1, 2);
        $resourceMock->expects($this->once())->method('duplicate')->with(1, 2);

        $this->assertEquals($duplicateMock, $this->_model->copy($this->productMock));
    }
}
