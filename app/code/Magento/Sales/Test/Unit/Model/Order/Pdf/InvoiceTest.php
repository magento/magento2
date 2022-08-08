<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * Tests Sales Order Invoice PDF model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $pdfConfigMock;

    /**
     * @var Database|MockObject
     */
    protected $databaseMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Write|MockObject
     */
    protected $directoryMock;

    /**
     * @var Renderer|MockObject
     */
    protected $addressRendererMock;

    /**
     * @var Data|MockObject
     */
    protected $paymentDataMock;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->pdfConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->createMock(Write::class);
        $this->directoryMock->expects($this->any())->method('getAbsolutePath')->willReturnCallback(
            function ($argument) {
                return BP . '/' . $argument;
            }
        );
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);

        $this->databaseMock = $this->createMock(Database::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->addressRendererMock = $this->createMock(Renderer::class);
        $this->paymentDataMock = $this->createMock(Data::class);
        $this->appEmulation = $this->createMock(Emulation::class);

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Sales\Model\Order\Pdf\Invoice::class,
            [
                'filesystem' => $filesystemMock,
                'pdfConfig' => $this->pdfConfigMock,
                'fileStorageDatabase' => $this->databaseMock,
                'scopeConfig' => $this->scopeConfigMock,
                'addressRenderer' => $this->addressRendererMock,
                'string' => new StringUtils(),
                'paymentData' => $this->paymentDataMock,
                'appEmulation' => $this->appEmulation
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetPdfInitRenderer(): void
    {
        $this->pdfConfigMock->expects(
            $this->once()
        )->method(
            'getRenderersPerProduct'
        )->with(
            'invoice'
        )->willReturn(
            [
                'product_type_one' => 'Renderer_Type_One_Product_One',
                'product_type_two' => 'Renderer_Type_One_Product_Two'
            ]
        );

        $this->model->getPdf([]);
        $renderers = new \ReflectionProperty($this->model, '_renderers');
        $renderers->setAccessible(true);
        $this->assertSame(
            [
                'product_type_one' => ['model' => 'Renderer_Type_One_Product_One', 'renderer' => null],
                'product_type_two' => ['model' => 'Renderer_Type_One_Product_Two', 'renderer' => null]
            ],
            $renderers->getValue($this->model)
        );
    }

    /**
     * @return void
     */
    public function testInsertLogoDatabaseMediaStorage(): void
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
        $this->pdfConfigMock->expects($this->once())
            ->method('getRenderersPerProduct')
            ->with('invoice')
            ->willReturn(['product_type_one' => 'Renderer_Type_One_Product_One']);
        $this->pdfConfigMock->expects($this->any())
            ->method('getTotals')
            ->willReturn([]);

        $block = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsSecureMode', 'toPdf'])
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
        $infoMock = $this->getMockForAbstractClass(InfoInterface::class);
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

        $this->scopeConfigMock
            ->method('getValue')
            ->withConsecutive(
                ['sales/identity/logo', ScopeInterface::SCOPE_STORE, null],
                ['sales/identity/address', ScopeInterface::SCOPE_STORE, null]
            )->willReturnOnConsecutiveCalls($filename, '');

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

        $this->model->getPdf([$invoiceMock]);
    }
}
