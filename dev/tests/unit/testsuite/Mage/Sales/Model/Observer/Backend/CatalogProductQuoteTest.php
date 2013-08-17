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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Model_Observer_Backend_CatalogProductQuoteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Model_Observer_Backend_CatalogProductQuote
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventMock;

    public function setUp()
    {
        $this->_quoteMock = $this->getMock('Mage_Sales_Model_Resource_Quote', array(), array(), '', false);
        $this->_observerMock = $this->getMock('Varien_Event_Observer', array(), array(), '', false);
        $this->_eventMock =
            $this->getMock('Varien_Event', array('getProduct', 'getStatus', 'getProductId'), array(), '', false);
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->_eventMock));
        $this->_model = new Mage_Sales_Model_Observer_Backend_CatalogProductQuote(
            $this->_quoteMock
        );
    }

    /**
     * @param int $productId
     * @param int $productStatus
     * @dataProvider statusUpdateDataProvider
     */
    public function testSaveProduct($productId, $productStatus)
    {
        $productMock = $this->getMock('Mage_Catalog_Model_Product', array('getId', 'getStatus'), array(), '', false);
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $productMock->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $productMock->expects($this->once())->method('getStatus')->will($this->returnValue($productStatus));
        $this->_quoteMock->expects($this->any())->method('markQuotesRecollect');
        $this->_model->catalogProductSaveAfter($this->_observerMock);
    }

    /**
     * @param int $productId
     * @param int $productStatus
     * @dataProvider statusUpdateDataProvider
     */
    public function testStatusUpdate($productId, $productStatus)
    {
        $this->_eventMock->expects($this->once())->method('getStatus')->will($this->returnValue($productStatus));
        $this->_eventMock->expects($this->once())->method('getProductId')->will($this->returnValue($productId));
        $this->_quoteMock->expects($this->any())->method('markQuotesRecollect');
        $this->_model->catalogProductStatusUpdate($this->_observerMock);
    }

    public function statusUpdateDataProvider()
    {
        return array(
            array(125, 1),
            array(100, 0)
        );
    }

    public function testSubtractQtyFromQuotes()
    {
        $productMock = $this->getMock('Mage_Catalog_Model_Product', array('getId', 'getStatus'), array(), '', false);
        $this->_eventMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $this->_quoteMock->expects($this->once())->method('substractProductFromQuotes')->with($productMock);
        $this->_model->subtractQtyFromQuotes($this->_observerMock);
    }
}
