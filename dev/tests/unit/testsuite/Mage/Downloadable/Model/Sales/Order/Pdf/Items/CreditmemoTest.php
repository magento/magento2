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
 * @category    Magento
 * @package     Mage_Downloadable
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Downloadable_Model_Sales_Order_Pdf_Items_CreditmemoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Downloadable_Model_Sales_Order_Pdf_Items_Creditmemo
     */
    protected $_model;

    /**
     * @var Mage_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_order;

    /**
     * @var Mage_Sales_Model_Order_Pdf_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdf;

    protected function setUp()
    {
        $objectManager = new Magento_Test_Helper_ObjectManager($this);
        $modelConstructorArgs = $objectManager->getConstructArguments(
            Magento_Test_Helper_ObjectManager::MODEL_ENTITY,
            'Mage_Sales_Model_Order'
        );

        $this->_order = $this->getMock('Mage_Sales_Model_Order', array('formatPriceTxt'), $modelConstructorArgs);
        $this->_order
            ->expects($this->any())
            ->method('formatPriceTxt')
            ->will($this->returnCallback(array($this, 'formatPrice')))
        ;

        $this->_pdf = $this->getMock('Mage_Sales_Model_Order_Pdf_Abstract', array('drawLineBlocks', 'getPdf'));

        $this->_model = $this->getMock(
            'Mage_Downloadable_Model_Sales_Order_Pdf_Items_Creditmemo',
            array('getLinks', 'getLinksTitle'),
            $modelConstructorArgs
        );
        $translator = $this->getMock('Mage_Core_Model_Translate', array(), array(), '', false, false);
        $this->_model->setStringHelper(new Mage_Core_Helper_String($translator));
        $this->_model->setOrder($this->_order);
        $this->_model->setPdf($this->_pdf);
        $this->_model->setPage(new Zend_Pdf_Page('a4'));
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_order = null;
        $this->_pdf = null;
    }

    /**
     * Return price formatted as a string including the currency sign
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return sprintf('$%.2F', $price);
    }

    public function testDraw()
    {
        $expectedPageSettings = array('table_header' => true);
        $expectedPdfPage = new Zend_Pdf_Page('a4');
        $expectedPdfData = array(array(
            'lines' => array(
                array(
                    array('text' => array('Downloadable Documentation'), 'feed' => 35),
                    array('text' => array('downloadable-docu', 'mentation'), 'feed' => 255, 'align' => 'right'),
                    array('text' => '$20.00',   'feed' => 330, 'font' => 'bold', 'align' => 'right'),
                    array('text' => '$-5.00',   'feed' => 380, 'font' => 'bold', 'align' => 'right'),
                    array('text' => '1',        'feed' => 445, 'font' => 'bold', 'align' => 'right'),
                    array('text' => '$2.00',    'feed' => 495, 'font' => 'bold', 'align' => 'right'),
                    array('text' => '$17.00',   'feed' => 565, 'font' => 'bold', 'align' => 'right'),
                ),
                array(
                    array('text' => array('Test Custom Option'), 'font' => 'italic', 'feed' => 35),
                ),
                array(
                    array('text' => array('test value'), 'feed' => 40),
                ),
                array(
                    array('text' => array('Download Links'), 'font' => 'italic', 'feed' => 35),
                ),
                array(
                    array('text' => array('Magento User Guide'), 'feed' => 40),
                ),
            ),
            'height' => 20,
        ));

        $this->_model->setItem(new Varien_Object(array(
            'name'              => 'Downloadable Documentation',
            'sku'               => 'downloadable-documentation',
            'row_total'         => 20.00,
            'discount_amount'   => 5.00,
            'qty'               => 1,
            'tax_amount'        => 2.00,
            'hidden_tax_amount' => 0.00,
            'order_item'        => new Varien_Object(array(
                'product_options' => array(
                    'options' => array(
                        array('label' => 'Test Custom Option', 'value' => 'test value'),
                    ),
                ),
            )),
        )));
        $this->_model
            ->expects($this->any())
            ->method('getLinksTitle')
            ->will($this->returnValue('Download Links'))
        ;
        $this->_model
            ->expects($this->any())
            ->method('getLinks')
            ->will($this->returnValue(new Varien_Object(array(
                'purchased_items' => array(
                    new Varien_Object(array('link_title' => 'Magento User Guide')),
                )
            ))))
        ;
        $this->_pdf
            ->expects($this->once())
            ->method('drawLineBlocks')
            ->with($this->anything(), $expectedPdfData, $expectedPageSettings)
            ->will($this->returnValue($expectedPdfPage))
        ;

        $this->assertNotSame($expectedPdfPage, $this->_model->getPage());
        $this->_model->draw();
        $this->assertSame($expectedPdfPage, $this->_model->getPage());
    }
}
