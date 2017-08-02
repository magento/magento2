<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;

/**
 * Class Pdfdocs
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Pdfdocs extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $fileFactory;

    /**
     * @var Invoice
     * @since 2.0.0
     */
    protected $pdfInvoice;

    /**
     * @var Shipment
     * @since 2.0.0
     */
    protected $pdfShipment;

    /**
     * @var Creditmemo
     * @since 2.0.0
     */
    protected $pdfCreditmemo;

    /**
     * @var DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @var ShipmentCollectionFactory
     * @since 2.0.0
     */
    protected $shipmentCollectionFactory;

    /**
     * @var InvoiceCollectionFactory
     * @since 2.0.0
     */
    protected $invoiceCollectionFactory;

    /**
     * @var CreditmemoCollectionFactory
     * @since 2.0.0
     */
    protected $creditmemoCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param Shipment $pdfShipment
     * @param Creditmemo $pdfCreditmemo
     * @param DateTime $dateTime
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        Shipment $pdfShipment,
        Creditmemo $pdfCreditmemo,
        DateTime $dateTime,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->pdfInvoice = $pdfInvoice;
        $this->pdfShipment = $pdfShipment;
        $this->pdfCreditmemo = $pdfCreditmemo;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->collectionFactory = $orderCollectionFactory;
        parent::__construct($context, $filter);
    }

    /**
     * Print all documents for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function massAction(AbstractCollection $collection)
    {
        $orderIds = $collection->getAllIds();

        $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $invoices = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $creditmemos = $this->creditmemoCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

        $documents = [];
        if ($invoices->getSize()) {
            $documents[] = $this->pdfInvoice->getPdf($invoices);
        }
        if ($shipments->getSize()) {
            $documents[] = $this->pdfShipment->getPdf($shipments);
        }
        if ($creditmemos->getSize()) {
            $documents[] = $this->pdfCreditmemo->getPdf($creditmemos);
        }

        if (empty($documents)) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }

        $pdf = array_shift($documents);
        foreach ($documents as $document) {
            $pdf->pages = array_merge($pdf->pages, $document->pages);
        }

        return $this->fileFactory->create(
            sprintf('docs%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
