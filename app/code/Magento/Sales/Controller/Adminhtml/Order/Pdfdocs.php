<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class Pdfdocs extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Print all documents for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function massAction(AbstractCollection $collection)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $flag = false;
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection->getItems() as $order) {
            $invoices = $order->getInvoiceCollection();
            if ($invoices->getSize()) {
                $flag = true;
                if (!isset($pdf)) {
                    $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice')
                        ->getPdf($invoices);
                } else {
                    $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice')
                        ->getPdf($invoices);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
            }

            $shipments = $order->getShipmentsCollection();
            if ($shipments->getSize()) {
                $flag = true;
                if (!isset($pdf)) {
                    $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')
                        ->getPdf($shipments);
                } else {
                    $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')
                        ->getPdf($shipments);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
            }

            $creditmemos = $order->getCreditmemosCollection();
            if ($creditmemos->getSize()) {
                $flag = true;
                if (!isset($pdf)) {
                    $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')
                        ->getPdf($creditmemos);
                } else {
                    $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')
                        ->getPdf($creditmemos);
                    $pdf->pages = array_merge($pdf->pages, $pages->pages);
                }
            }
        }
        if ($flag) {
            $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')
                ->date('Y-m-d_H-i-s');
            return $this->fileFactory->create(
                'docs' . $date . '.pdf',
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            $resultRedirect->setPath('sales/*/');
            return $resultRedirect;
        }
    }
}
