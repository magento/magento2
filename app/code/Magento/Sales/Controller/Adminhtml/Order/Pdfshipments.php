<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Pdfshipments extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Print shipments for selected orders
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $shipments = $this->_objectManager->create(
                    'Magento\Sales\Model\Resource\Order\Shipment\Collection'
                )->setOrderFilter(
                    $orderId
                )->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)) {
                        $pdf = $this->_objectManager->create(
                            'Magento\Sales\Model\Order\Pdf\Shipment'
                        )->getPdf(
                            $shipments
                        );
                    } else {
                        $pages = $this->_objectManager->create(
                            'Magento\Sales\Model\Order\Pdf\Shipment'
                        )->getPdf(
                            $shipments
                        );
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
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
                $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
                $this->_redirect('sales/*/');
            }
        }
        $this->_redirect('sales/*/');
    }
}
