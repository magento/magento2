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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Observer\Backend;

class CatalogProductQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\CatalogProductQuote
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventMock;

    protected function setUp()
    {
        $this->_quoteMock = $this->getMock('Magento\Sales\Model\Resource\Quote', array(), array(), '', false);
        $this->_observerMock = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);
        $this->_eventMock = $this->getMock(
            'Magento\Framework\Event',
            array('getProduct', 'getStatus', 'getProductId'),
            array(),
            '',
            false
        );
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->_eventMock));
        $this->_model = new \Magento\Sales\Model\Observer\Backend\CatalogProductQuote($this->_quoteMock);
    }

    /**
     * @param int $productId
     * @param int $productStatus
     * @dataProvider statusUpdateDataProvider
     */
    public function testSaveProduct($productId, $productStatus)
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getId', 'getStatus', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $productMock->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->once())->method('getStatus')->will($this->returnValue($productStatus));
        $this->_quoteMock->expects($this->any())->method('markQuotesRecollect');
        $this->_model->catalogProductSaveAfter($this->_observerMock);
    }

    public function statusUpdateDataProvider()
    {
        return array(array(125, 1), array(100, 0));
    }

    public function testSubtractQtyFromQuotes()
    {
        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getId', 'getStatus', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $this->_quoteMock->expects($this->once())->method('substractProductFromQuotes')->with($productMock);
        $this->_model->subtractQtyFromQuotes($this->_observerMock);
    }
}
