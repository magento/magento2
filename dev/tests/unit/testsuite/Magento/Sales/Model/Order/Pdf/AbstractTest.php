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
namespace Magento\Sales\Model\Order\Pdf;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test protected method to reduce testing complexity, which would be too high in case of testing a public method
     * without completing a huge refactoring of the class.
     */
    public function testInsertTotals()
    {
        // Setup parameters, that will be passed to the tested model method
        $page = $this->getMock('Zend_Pdf_Page', array(), array(), '', false);

        $order = new \StdClass();
        $source = $this->getMock('Magento\Sales\Model\Order\Invoice', array(), array(), '', false);
        $source->expects($this->any())->method('getOrder')->will($this->returnValue($order));

        // Setup most constructor dependencies
        $paymentData = $this->getMock('Magento\Payment\Helper\Data', array(), array(), '', false);
        $string = $this->getMock('Magento\Framework\Stdlib\String', array(), array(), '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $translate = $this->getMock('Magento\Framework\Translate\Inline\StateInterface', array(), array(), '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $pdfItemsFactory = $this->getMock('Magento\Sales\Model\Order\Pdf\ItemsFactory', array(), array(), '', false);
        $localeMock = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            array(),
            array(),
            '',
            false,
            false
        );

        // Setup config file totals
        $configTotals = array('item1' => array(''), 'item2' => array('model' => 'custom_class'));
        $pdfConfig = $this->getMock('Magento\Sales\Model\Order\Pdf\Config', array(), array(), '', false);
        $pdfConfig->expects($this->once())->method('getTotals')->will($this->returnValue($configTotals));

        // Setup total factory
        $total1 = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            array('setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay'),
            array(),
            '',
            false
        );
        $total1->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $total1->expects($this->once())->method('setSource')->with($source)->will($this->returnSelf());
        $total1->expects($this->once())->method('canDisplay')->will($this->returnValue(true));
        $total1->expects($this->once())
            ->method('getTotalsForDisplay')
            ->will($this->returnValue(array(array('label' => 'label1', 'font_size' => 1, 'amount' => '$1'))));

        $total2 = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            array('setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay'),
            array(),
            '',
            false
        );
        $total2->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $total2->expects($this->once())->method('setSource')->with($source)->will($this->returnSelf());
        $total2->expects($this->once())->method('canDisplay')->will($this->returnValue(true));
        $total2->expects($this->once())
            ->method('getTotalsForDisplay')
            ->will($this->returnValue(array(array('label' => 'label2', 'font_size' => 2, 'amount' => '$2'))));

        $valueMap = array(array(null, array(), $total1), array('custom_class', array(), $total2));
        $pdfTotalFactory = $this->getMock('Magento\Sales\Model\Order\Pdf\Total\Factory', array(), array(), '', false);
        $pdfTotalFactory->expects($this->exactly(2))->method('create')->will($this->returnValueMap($valueMap));

        // Test model
        /** @var \Magento\Sales\Model\Order\Pdf\AbstractPdf $model */
        $model = $this->getMockForAbstractClass(
            'Magento\Sales\Model\Order\Pdf\AbstractPdf',
            array(
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate
            ),
            '',
            true,
            false,
            true,
            array('drawLineBlocks')
        );
        $model->expects($this->once())->method('drawLineBlocks')->will($this->returnValue($page));

        $reflectionMethod = new \ReflectionMethod('Magento\Sales\Model\Order\Pdf\AbstractPdf', 'insertTotals');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($model, $page, $source);

        $this->assertSame($page, $actual);
    }
}
