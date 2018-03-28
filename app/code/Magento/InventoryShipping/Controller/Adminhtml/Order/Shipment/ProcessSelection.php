<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class ProcessSelection extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $sourceSelection = $request->getParam('source_selection');
        $orderId = $sourceSelection['order_id']??3;
        $sourceCode = $sourceSelection['source_code']?? 'default';
       // source_selection[items][5][default]
        $shipmentItems = [];
        if ($request->isPost() /*&& !empty($sourceSelection['items'])*/) {
/*            foreach ($sourceSelection['items'] as $orderItemId => $data) {
                $shipmentItems[$orderItemId] = $data[$sourceCode];
            }*/
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath(                'adminhtml/order_shipment/new',
                [
                    'order_id' => $orderId,
                    'shipment' => $shipmentItems
                ]);

            return $redirect;
            $this->getRequest()->setParams(['shipment' => $shipmentItems]);
            $this->_redirect(
                'adminhtml/order_shipment/new',
                [
                    'order_id' => $orderId,
                    'shipment' => $shipmentItems
                ]
            );


        }
    }
}