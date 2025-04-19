<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Sales\Model\Order\Shipment\Comment as ShipmentComment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment as ShipmentCommentResource;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

/**
 * Shipment add comment
 */
class AddComment extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentCommentSender
     */
    protected $shipmentCommentSender;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var ShipmentComment
     */
    protected $shipmentComment;

    /**
     * @var ShipmentCommentResource
     */
    protected $shipmentCommentResource;

    /**
     * @param Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param ShipmentCommentSender $shipmentCommentSender
     * @param LayoutFactory $resultLayoutFactory
     * @param ShipmentComment|null $shipmentComment
     * @param ShipmentCommentResource|null $shipmentCommentResource
     */
    public function __construct(
        Action\Context $context,
        ShipmentLoader $shipmentLoader,
        ShipmentCommentSender $shipmentCommentSender,
        LayoutFactory $resultLayoutFactory,
        ?ShipmentComment $shipmentComment = null,
        ?ShipmentCommentResource $shipmentCommentResource = null
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentCommentSender = $shipmentCommentSender;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->shipmentComment = $shipmentComment ??
            ObjectManager::getInstance()->get(ShipmentComment::class);
        $this->shipmentCommentResource = $shipmentCommentResource ??
            ObjectManager::getInstance()->get(ShipmentCommentResource::class);
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

            if (empty($data['comment_id'])) {
                $shipment->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
                $this->shipmentCommentSender->send($shipment, !empty($data['is_customer_notified']), $data['comment']);
                $shipment->save();
            } else {
                $comment = $this->shipmentComment->setComment($data['comment'])->setId($data['comment_id']);
                $comment->setShipment($shipment);
                $this->shipmentCommentResource->save($comment);
            }

            $resultLayout = $this->resultLayoutFactory->create();
            $resultLayout->addDefaultHandle();
            $response = $resultLayout->getLayout()->getBlock('shipment_comments')->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get(Data::class)->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
