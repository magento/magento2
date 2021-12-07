<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ShipmentTest
 *
 * Tests Sales Order Shipment PDF model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $model;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config|MockObject
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
     * @var \Magento\Framework\Filesystem\Directory\Write|MockObject
     */
    protected $directoryMock;

    /**
     * @var Renderer|MockObject
     */
    protected $addressRendererMock;

    /**
     * @var \Magento\Payment\Helper\Data|MockObject
     */
    protected $paymentDataMock;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->pdfConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->directoryMock->expects($this->any())->method('getAbsolutePath')->will(
            $this->returnCallback(
                function ($argument) {
                    return BP . '/' . $argument;
                }
            )
        );
        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryMock));
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryMock));

        $this->databaseMock = $this->createMock(Database::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->addressRendererMock = $this->createMock(Renderer::class);
        $this->paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->appEmulation = $this->createMock(\Magento\Store\Model\App\Emulation::class);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Sales\Model\Order\Pdf\Shipment::class,
            [
                'filesystem' => $filesystemMock,
                'pdfConfig' => $this->pdfConfigMock,
                'fileStorageDatabase' => $this->databaseMock,
                'scopeConfig' => $this->scopeConfigMock,
                'addressRenderer' => $this->addressRendererMock,
                'string' => new \Magento\Framework\Stdlib\StringUtils(),
                'paymentData' => $this->paymentDataMock,
                'appEmulation' => $this->appEmulation
            ]
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
            ->with('shipment')
            ->will($this->returnValue(['product_type_one' => 'Renderer_Type_One_Product_One']));
        $this->pdfConfigMock->expects($this->any())
            ->method('getTotals')
            ->will($this->returnValue([]));

        $block = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsSecureMode', 'toPdf'])
            ->getMock();
        $block->expects($this->any())
            ->method('setIsSecureMode')
            ->willReturn($block);
        $block->expects($this->any())
            ->method('toPdf')
            ->will($this->returnValue(''));
        $this->paymentDataMock->expects($this->any())
            ->method('getInfoBlock')
            ->willReturn($block);

        $this->addressRendererMock->expects($this->any())
            ->method('format')
            ->will($this->returnValue(''));

        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(true));

        $shipmentMock = $this->createMock(Shipment::class);
        $orderMock = $this->createMock(Order::class);
        $addressMock = $this->createMock(Address::class);
        $orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($addressMock);
        $orderMock->expects($this->any())
            ->method('getIsVirtual')
            ->will($this->returnValue(true));
        $infoMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);
        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($infoMock);
        $shipmentMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $shipmentMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $shipmentMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([]);

        $this->scopeConfigMock
            ->method('getValue')
            ->withConsecutive(
                ['sales/identity/logo', ScopeInterface::SCOPE_STORE, null],
                ['sales/identity/address', ScopeInterface::SCOPE_STORE, null]
            )->willReturnOnConsecutiveCalls($this->returnValue($filename), $this->returnValue(null));

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

        $this->model->getPdf([$shipmentMock]);
    }
}
