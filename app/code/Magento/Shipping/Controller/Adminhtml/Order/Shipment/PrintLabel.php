<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;
use Zend_Pdf;

class PrintLabel extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Action\Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param LabelGenerator $labelGenerator
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader,
        protected readonly LabelGenerator $labelGenerator,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Print label for one specific shipment
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            $labelContent = $shipment->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new Zend_Pdf();
                    $page = $this->labelGenerator->createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->messageManager->addError(
                            __(
                                'We don\'t recognize or support the file extension in this shipment: %1.',
                                $shipment->getIncrementId()
                            )
                        );
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_fileFactory->create(
                    'ShippingLabel(' . $shipment->getIncrementId() . ').pdf',
                    $pdfContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $this->messageManager->addError(__('An error occurred while creating shipping label.'));
        }
        $this->_redirect(
            'adminhtml/order_shipment/view',
            ['shipment_id' => $this->getRequest()->getParam('shipment_id')]
        );
    }
}
