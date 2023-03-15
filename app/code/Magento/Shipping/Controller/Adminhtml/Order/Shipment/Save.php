<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;

/**
 * Controller for generation of new Shipments from Backend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @param ActionContext $context
     * @param ShipmentLoader $shipmentLoader
     * @param LabelGenerator $labelGenerator
     * @param ShipmentSender $shipmentSender
     * @param ShipmentValidatorInterface|null $shipmentValidator
     * @param SalesData $salesData
     */
    public function __construct(
        ActionContext $context,
        protected readonly ShipmentLoader $shipmentLoader,
        protected readonly LabelGenerator $labelGenerator,
        protected readonly ShipmentSender $shipmentSender,
        private ?ShipmentValidatorInterface $shipmentValidator = null,
        private ?SalesData $salesData = null
    ) {
        parent::__construct($context);

        $this->shipmentValidator = $shipmentValidator ?: ObjectManager::getInstance()
            ->get(ShipmentValidatorInterface::class);
        $this->salesData = $salesData ?: ObjectManager::getInstance()
            ->get(SalesData::class);
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param OrderShipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * Save shipment
     *
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        $data = $this->getRequest()->getParam('shipment');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get(BackendSession::class)->setCommentText($data['comment_text']);
        }

        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
        $responseAjax = new DataObject();

        try {
            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($data);
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
            }

            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $shipment->setCustomerNote($data['comment_text']);
                $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }
            $validationResult = $this->shipmentValidator->validate($shipment, [QuantityValidator::class]);

            if ($validationResult->hasMessages()) {
                $this->messageManager->addErrorMessage(
                    __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                );
                return $resultRedirect->setPath('*/*/new', ['order_id' => $orderId]);
            }
            $shipment->register();

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

            if ($isNeedCreateLabel) {
                $this->labelGenerator->create($shipment, $this->_request);
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            if (!empty($data['send_email']) && $this->salesData->canSendNewShipmentEmail()) {
                $this->shipmentSender->send($shipment);
            }

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage = __('You created the shipping label.');

            $this->messageManager->addSuccessMessage(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
            $this->_objectManager->get(BackendSession::class)->getCommentText(true);
        } catch (LocalizedException $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/new', ['order_id' => $orderId]);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
            } else {
                $this->messageManager->addErrorMessage(__('Cannot save shipment.'));
                return $resultRedirect->setPath('*/*/new', ['order_id' => $orderId]);
            }
        }
        if ($isNeedCreateLabel) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setJsonData($responseAjax->toJson());
        }

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
