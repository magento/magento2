<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\ScopeInterface;
use Zend_Pdf;
use Zend_Pdf_Image;
use Zend_Pdf_Page;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelGenerator
{
    /**
     * @param CarrierFactory $carrierFactory
     * @param LabelsFactory $labelFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param TrackFactory $trackFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        protected readonly CarrierFactory $carrierFactory,
        protected readonly LabelsFactory $labelFactory,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly TrackFactory $trackFactory,
        protected readonly Filesystem $filesystem
    ) {
    }

    /**
     * @param OrderShipment $shipment
     * @param RequestInterface $request
     * @return void
     * @throws LocalizedException
     */
    public function create(OrderShipment $shipment, RequestInterface $request)
    {
        $order = $shipment->getOrder();
        $carrier = $this->carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if (!$carrier->isShippingLabelsAvailable()) {
            throw new LocalizedException(__('Shipping labels is not available.'));
        }
        $shipment->setPackages($request->getParam('packages'));
        $response = $this->labelFactory->create()->requestToShipment($shipment);
        if ($response->hasErrors()) {
            throw new LocalizedException(__($response->getErrors()));
        }
        if (!$response->hasInfo()) {
            throw new LocalizedException(__('Response info is not exist.'));
        }
        $labelsContent = [];
        $trackingNumbers = [];
        $info = $response->getInfo();
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }
        $outputPdf = $this->combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render());
        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = $this->scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            ScopeInterface::SCOPE_STORE,
            $shipment->getStoreId()
        );
        if (!empty($trackingNumbers)) {
            $this->addTrackingNumbersToShipment($shipment, $trackingNumbers, $carrierCode, $carrierTitle);
        }
    }

    /**
     * @param OrderShipment $shipment
     * @param array $trackingNumbers
     * @param string $carrierCode
     * @param string $carrierTitle
     *
     * @return void
     */
    private function addTrackingNumbersToShipment(
        OrderShipment $shipment,
        $trackingNumbers,
        $carrierCode,
        $carrierTitle
    ) {
        foreach ($trackingNumbers as $number) {
            if (is_array($number)) {
                $this->addTrackingNumbersToShipment($shipment, $number, $carrierCode, $carrierTitle);
            } else {
                $shipment->addTrack(
                    $this->trackFactory->create()
                        ->setNumber($number)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle)
                );
            }
        }
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    public function combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }

    /**
     * Create \Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return Zend_Pdf_Page|false
     */
    public function createPdfPageFromImageString($imageString)
    {
        /** @var DirectoryWrite $directory */
        $directory = $this->filesystem->getDirectoryWrite(
            DirectoryList::TMP
        );
        $directory->create();
        $image = @imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = $directory->getAbsolutePath(
            'shipping_labels_' . uniqid(MathRandom::getRandomNumber()) . time() . '.png'
        );
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        $directory->delete($directory->getRelativePath($tmpFileName));
        if (is_resource($image)) {
            imagedestroy($image);
        }
        return $page;
    }
}
