<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class InvoiceTest
 *
 * Tests Sales Order Invoice PDF model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $_model;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_pdfConfigMock;

    /**
     * @var Database|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $databaseMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directoryMock;

    /**
     * @var Renderer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressRendererMock;

    /**
     * @var \Magento\Payment\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentDataMock;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    protected function setUp(): void
    {
        $this->_pdfConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->directoryMock->expects($this->any())->method('getAbsolutePath')->willReturnCallback(
            
                function ($argument) {
                    return BP . '/' . $argument;
                }
            
        );
        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);

        $this->databaseMock = $this->createMock(Database::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->addressRendererMock = $this->createMock(Renderer::class);
        $this->paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->appEmulation = $this->createMock(\Magento\Store\Model\App\Emulation::class);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            \Magento\Sales\Model\Order\Pdf\Invoice::class,
            [
                'filesystem' => $filesystemMock,
                'pdfConfig' => $this->_pdfConfigMock,
                'fileStorageDatabase' => $this->databaseMock,
                'scopeConfig' => $this->scopeConfigMock,
                'addressRenderer' => $this->addressRendererMock,
                'string' => new \Magento\Framework\Stdlib\StringUtils(),
                'paymentData' => $this->paymentDataMock,
                'appEmulation' => $this->appEmulation
            ]
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
        )->willReturn(
            
                [
                    'product_type_one' => 'Renderer_Type_One_Product_One',
                    'product_type_two' => 'Renderer_Type_One_Product_Two',
                ]
            
        );

        $this->_model->getPdf([]);
        $renderers = new \ReflectionProperty($this->_model, '_renderers');
        $renderers->setAccessible(true);
        $this->assertSame(
            [
                'product_type_one' => ['model' => 'Renderer_Type_One_Product_One', 'renderer' => null],
                'product_type_two' => ['model' => 'Renderer_Type_One_Product_Two', 'renderer' => null],
            ],
            $renderers->getValue($this->_model)
        );
    }

    public function testInsertLogoDatabaseMediaStorage()
    {
        $filename = 'image.jpg';
        $path = '/sales/store/logo/';
        $storeId = 1;

        $this->appEmulation->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with(
                $storeId,
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            )
            ->willReturnSelf();
        $this->appEmulation->expects($this->once())
            ->method('stopEnvironmentEmulation')
            ->willReturnSelf();
        $this->_pdfConfigMock->expects($this->once())
            ->method('getRenderersPerProduct')
            ->with('invoice')
            ->willReturn(['product_type_one' => 'Renderer_Type_One_Product_One']);
        $this->_pdfConfigMock->expects($this->any())
            ->method('getTotals')
            ->willReturn([]);

        $block = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsSecureMode','toPdf'])
            ->getMock();
        $block->expects($this->any())
            ->method('setIsSecureMode')
            ->willReturn($block);
        $block->expects($this->any())
            ->method('toPdf')
            ->willReturn('');
        $this->paymentDataMock->expects($this->any())
            ->method('getInfoBlock')
            ->willReturn($block);

        $this->addressRendererMock->expects($this->any())
            ->method('format')
            ->willReturn('');

        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->willReturn(true);

        $invoiceMock = $this->createMock(Invoice::class);
        $orderMock = $this->createMock(Order::class);
        $addressMock = $this->createMock(Address::class);
        $orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($addressMock);
        $orderMock->expects($this->any())
            ->method('getIsVirtual')
            ->willReturn(true);
        $infoMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);
        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($infoMock);
        $invoiceMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $invoiceMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([]);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with('sales/identity/logo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($filename);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('sales/identity/address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn('');

        $this->directoryMock->expects($this->any())
            ->method('isFile')
            ->with($path . $filename)
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(false),
                $this->returnValue(false)
            );

        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->with($path . $filename);

        $this->_model->getPdf([$invoiceMock]);
    }
}
