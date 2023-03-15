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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Order\Pdf\Packaging as PdfPackaging;
use Zend_Pdf;

class PrintPackage extends Action
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
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Create pdf document with information about packages
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
        $shipment = $this->shipmentLoader->load();

        if ($shipment) {
            /** @var Zend_Pdf $pdf */
            $pdf = $this->_objectManager->create(PdfPackaging::class)->getPdf($shipment);
            return $this->_fileFactory->create(
                'packingslip' . $this->_objectManager->get(
                    DateTime::class
                )->date(
                    'Y-m-d_H-i-s'
                ) . '.pdf',
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->_forward('noroute');
        }
    }
}
