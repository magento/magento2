<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as Json;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

class AddComment extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @param Action\Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param ShipmentCommentSender $shipmentCommentSender
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        protected readonly ShipmentLoader $shipmentLoader,
        protected readonly ShipmentCommentSender $shipmentCommentSender,
        protected readonly LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Add comment to shipment history
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('shipment_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            $shipment->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );

            $this->shipmentCommentSender->send($shipment, !empty($data['is_customer_notified']), $data['comment']);
            $shipment->save();
            $resultLayout = $this->resultLayoutFactory->create();
            $resultLayout->addDefaultHandle();
            $response = $resultLayout->getLayout()->getBlock('shipment_comments')->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get(Json::class)->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
