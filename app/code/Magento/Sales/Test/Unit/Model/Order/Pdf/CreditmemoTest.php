<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Pdf\Config as PdfConfig;
use Magento\Sales\Model\Order\Pdf\Creditmemo as CreditmemoPdf;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Class CreditmemoTest
 *
 * Tests Sales Order Creditmemo PDF model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CreditmemoPdf
     */
    protected $model;

    /**
     * @var PdfConfig|MockObject
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
     * @var DirectoryWrite|MockObject
     */
    protected $directoryMock;

    /**
     * @var Renderer|MockObject
     */
    protected $addressRendererMock;

    /**
     * @var PaymentData|MockObject
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
        $this->pdfConfigMock = $this->createMock(PdfConfig::class);
        $this->directoryMock = $this->createMock(DirectoryWrite::class);
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
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->addressRendererMock = $this->createMock(Renderer::class);
        $this->paymentDataMock = $this->createMock(PaymentData::class);
        $this->appEmulation = $this->createMock(Emulation::class);

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            CreditmemoPdf::class,
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
    public function testInsertLogoDatabaseMediaStorage(): void
    {
        $filename = 'image.jpg';
        $path = '/sales/store/logo/';
        $storeId = 1;

        $this->appEmulation->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with(
                $storeId,
                Area::AREA_FRONTEND,
                true
            )
            ->willReturnSelf();
        $this->appEmulation->expects($this->once())
            ->method('stopEnvironmentEmulation')
            ->willReturnSelf();
        $this->pdfConfigMock->expects($this->once())
            ->method('getRenderersPerProduct')
            ->with('creditmemo')
            ->willReturn(['product_type_one' => 'Renderer_Type_One_Product_One']);
        $this->pdfConfigMock->expects($this->any())
            ->method('getTotals')
            ->willReturn([]);

        $block = $this->createPartialMockWithReflection(
            Template::class,
            ['setIsSecureMode', 'toPdf']
        );
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

        $creditmemoMock = $this->createMock(Creditmemo::class);
        $orderMock = $this->createMock(Order::class);
        $addressMock = $this->createMock(Address::class);
        $orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($addressMock);
        $orderMock->expects($this->any())
            ->method('getIsVirtual')
            ->willReturn(true);
        $infoMock = $this->createMock(InfoInterface::class);
        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($infoMock);
        $creditmemoMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([]);

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($filename) {
                    if ($arg1 === 'sales/identity/logo' &&
                        $arg2 === ScopeInterface::SCOPE_STORE && $arg3 === null) {
                        return $filename;
                    } elseif ($arg1 === 'sales/identity/address' &&
                        $arg2 === ScopeInterface::SCOPE_STORE && $arg3 === null) {
                        return '';
                    }
                }
            );

        $this->directoryMock->expects($this->any())
            ->method('isFile')
            ->with($path . $filename)
            ->willReturnOnConsecutiveCalls(false, false);

        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->with($path . $filename);

        $this->model->getPdf([$creditmemoMock]);
    }
}
