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
namespace Magento\Catalog\Model\Product\CopyConstructor;

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

        $this->_productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->_duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('setCrossSellLinkData', '__wakeup'),
            array(),
            '',
            false
        );

        $this->_linkMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Link',
            array('__wakeup', 'getAttributes', 'getCrossSellLinkCollection', 'useCrossSellLinks'),
            array(),
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
        $expectedData = array('100500' => array('some' => 'data'));

        $attributes = array('attributeOne' => array('code' => 'one'), 'attributeTwo' => array('code' => 'two'));

        $this->_linkMock->expects($this->once())->method('useCrossSellLinks');

        $this->_linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $productLinkMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Link',
            array('__wakeup', 'getLinkedProductId', 'toArray'),
            array(),
            '',
            false
        );

        $productLinkMock->expects($this->once())->method('getLinkedProductId')->will($this->returnValue('100500'));
        $productLinkMock->expects(
            $this->once()
        )->method(
            'toArray'
        )->with(
            array('one', 'two')
        )->will(
            $this->returnValue(array('some' => 'data'))
        );

        $collectionMock = $helper->getCollectionMock(
            '\Magento\Catalog\Model\Resource\Product\Link\Collection',
            array($productLinkMock)
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
