<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintPackage extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->shipmentLoader = $shipmentLoader;
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
            /** @var \Zend_Pdf $pdf */
            $pdf = $this->_objectManager->create('Magento\Shipping\Model\Order\Pdf\Packaging')->getPdf($shipment);
            return $this->_fileFactory->create(
                'packingslip' . $this->_objectManager->get(
                    'Magento\Framework\Stdlib\DateTime\DateTime'
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
