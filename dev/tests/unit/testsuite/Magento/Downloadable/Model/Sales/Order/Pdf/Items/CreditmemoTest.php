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
namespace Magento\Downloadable\Model\Sales\Order\Pdf\Items;

class CreditmemoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo
     */
    protected $_model;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\AbstractPdf|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdf;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false),
            'orderItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Item\CollectionFactory',
                array(),
                array(),
                '',
                false
            ),
            'serviceOrderFactory' => $this->getMock(
                'Magento\Sales\Model\Service\OrderFactory',
                array(),
                array(),
                '',
                false
            ),
            'currencyFactory' => $this->getMock(
                'Magento\Directory\Model\CurrencyFactory',
                array(),
                array(),
                '',
                false
            ),
            'orderHistoryFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Status\HistoryFactory',
                array(),
                array(),
                '',
                false
            ),
            'orderTaxCollectionFactory' => $this->getMock(
                'Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory',
                array(),
                array(),
                '',
                false
            )
        );
        $orderConstructorArgs = $objectManager->getConstructArguments('Magento\Sales\Model\Order', $arguments);
        $this->_order = $this->getMock('Magento\Sales\Model\Order', array('formatPriceTxt'), $orderConstructorArgs);
        $this->_order->expects(
            $this->any()
        )->method(
            'formatPriceTxt'
        )->will(
            $this->returnCallback(array($this, 'formatPrice'))
        );

        $this->_pdf = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\AbstractPdf',
            array('drawLineBlocks', 'getPdf'),
            array(),
            '',
            false,
            false
        );

        $filterManager = $this->getMock(
            'Magento\Framework\Filter\FilterManager',
            array('stripTags'),
            array(),
            '',
            false
        );
        $filterManager->expects($this->any())->method('stripTags')->will($this->returnArgument(0));

        $modelConstructorArgs = $objectManager->getConstructArguments(
            'Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo',
            array('string' => new \Magento\Framework\Stdlib\String(), 'filterManager' => $filterManager)
        );

        $this->_model = $this->getMock(
            'Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo',
            array('getLinks', 'getLinksTitle'),
            $modelConstructorArgs
        );

        $this->_model->setOrder($this->_order);
        $this->_model->setPdf($this->_pdf);
        $this->_model->setPage(new \Zend_Pdf_Page('a4'));
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
        $expectedPdfPage = new \Zend_Pdf_Page('a4');
        $expectedPdfData = array(
            array(
                'lines' => array(
                    array(
                        array('text' => array('Downloadable Documentation'), 'feed' => 35),
                        array('text' => array('downloadable-docu', 'mentation'), 'feed' => 255, 'align' => 'right'),
                        array('text' => '$20.00', 'feed' => 330, 'font' => 'bold', 'align' => 'right'),
                        array('text' => '$-5.00', 'feed' => 380, 'font' => 'bold', 'align' => 'right'),
                        array('text' => '1', 'feed' => 445, 'font' => 'bold', 'align' => 'right'),
                        array('text' => '$2.00', 'feed' => 495, 'font' => 'bold', 'align' => 'right'),
                        array('text' => '$17.00', 'feed' => 565, 'font' => 'bold', 'align' => 'right')
                    ),
                    array(array('text' => array('Test Custom Option'), 'font' => 'italic', 'feed' => 35)),
                    array(array('text' => array('test value'), 'feed' => 40)),
                    array(array('text' => array('Download Links'), 'font' => 'italic', 'feed' => 35)),
                    array(array('text' => array('Magento User Guide'), 'feed' => 40))
                ),
                'height' => 20
            )
        );

        $this->_model->setItem(
            new \Magento\Framework\Object(
                array(
                    'name' => 'Downloadable Documentation',
                    'sku' => 'downloadable-documentation',
                    'row_total' => 20.00,
                    'discount_amount' => 5.00,
                    'qty' => 1,
                    'tax_amount' => 2.00,
                    'hidden_tax_amount' => 0.00,
                    'order_item' => new \Magento\Framework\Object(
                        array(
                            'product_options' => array(
                                'options' => array(array('label' => 'Test Custom Option', 'value' => 'test value'))
                            )
                        )
                    )
                )
            )
        );
        $this->_model->expects($this->any())->method('getLinksTitle')->will($this->returnValue('Download Links'));
        $this->_model->expects(
            $this->any()
        )->method(
            'getLinks'
        )->will(
            $this->returnValue(
                new \Magento\Framework\Object(
                    array('purchased_items' => array(
                        new \Magento\Framework\Object(array('link_title' => 'Magento User Guide')))
                    )
                )
            )
        );
        $this->_pdf->expects(
            $this->once()
        )->method(
            'drawLineBlocks'
        )->with(
            $this->anything(),
            $expectedPdfData,
            $expectedPageSettings
        )->will(
            $this->returnValue($expectedPdfPage)
        );

        $this->assertNotSame($expectedPdfPage, $this->_model->getPage());
        $this->assertNull($this->_model->draw());
        $this->assertSame($expectedPdfPage, $this->_model->getPage());
    }
}
