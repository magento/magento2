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

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $_model;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdfConfigMock;

    protected function setUp()
    {
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', array(), array(), '', false);
        $stringMock = $this->getMock('Magento\Stdlib\String', array(), array(), '', false, false);
        $storeConfigMock = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false, false);
        $translateMock = $this->getMock('Magento\Translate\Inline\StateInterface', array(), array(), '', false, false);
        $directoryMock = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false, false);
        $directoryMock->expects($this->any())->method('getAbsolutePath')->will(
            $this->returnCallback(
                function ($argument) {
                    return BP . '/' . $argument;
                }
            )
        );
        $filesystemMock = $this->getMock('Magento\App\Filesystem', array(), array(), '', false, false);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($directoryMock));
        $filesystemMock->expects($this->any())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

        $this->_pdfConfigMock = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Config',
            array(),
            array(),
            '',
            false,
            false
        );
        $totalFactoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\Factory',
            array(),
            array(),
            '',
            false,
            false
        );
        $pdfItemsFactoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\ItemsFactory',
            array(),
            array(),
            '',
            false,
            false
        );
        $localeDateMock = $this->getMock(
            'Magento\Stdlib\DateTime\TimezoneInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $storeManagerMock = $this->getMock(
            'Magento\Core\Model\StoreManagerInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $localeResolverMock = $this->getMock('Magento\Locale\ResolverInterface', array(), array(), '', false, false);

        $this->_model = new \Magento\Sales\Model\Order\Pdf\Invoice(
            $paymentDataMock,
            $stringMock,
            $storeConfigMock,
            $filesystemMock,
            $this->_pdfConfigMock,
            $totalFactoryMock,
            $pdfItemsFactoryMock,
            $localeDateMock,
            $translateMock,
            $storeManagerMock,
            $localeResolverMock,
            array()
        );
    }

    public function testGetPdfInitRenderer()
    {
        $this->_pdfConfigMock->expects(
            $this->once()
        )->method(
            'getRenderersPerProduct'
        )->with(
            'invoice'
        )->will(
            $this->returnValue(
                array(
                    'product_type_one' => 'Renderer_Type_One_Product_One',
                    'product_type_two' => 'Renderer_Type_One_Product_Two'
                )
            )
        );

        $this->_model->getPdf(array());
        $renderers = new \ReflectionProperty($this->_model, '_renderers');
        $renderers->setAccessible(true);
        $this->assertSame(
            array(
                'product_type_one' => array('model' => 'Renderer_Type_One_Product_One', 'renderer' => null),
                'product_type_two' => array('model' => 'Renderer_Type_One_Product_Two', 'renderer' => null)
            ),
            $renderers->getValue($this->_model)
        );
    }
}
