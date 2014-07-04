<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;

/**
 * Adminhtml order shipment controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipment extends \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $fileFactory);
    }

    /**
     * Initialize shipment items QTY
     *
     * @return array
     */
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    /**
     * Initialize shipment model instance
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Sales\Model\Order\Shipment|bool
     */
    protected function _initShipment()
    {
        $this->_title->add(__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->messageManager->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->messageManager->addError(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->messageManager->addError(__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = $this->_objectManager->create(
                'Magento\Sales\Model\Service\Order',
                array('order' => $order)
            )->prepareShipment(
                $savedQtys
            );

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        throw new \Magento\Framework\Model\Exception(__('Please enter a tracking number.'));
                    }
                    $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        $this->_coreRegistry->register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        )->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * Shipment information page
     *
     * @return void
     */
    public function viewAction()
    {
        $shipment = $this->_initShipment();
        if ($shipment) {
            $this->_title->add("#" . $shipment->getIncrementId());
            $this->_view->loadLayout();
            $this->_view->getLayout()->getBlock(
                'sales_shipment_view'
            )->updateBackButtonUrl(
                $this->getRequest()->getParam('come_from')
            );
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->renderLayout();
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Start create shipment action
     *
     * @return void
     */
    public function startAction()
    {
        /**
         * Clear old values for shipment qty's
         */
        $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    /**
     * Shipment create page
     *
     * @return void
     */
    public function newAction()
    {
        $shipment = $this->_initShipment();
        if ($shipment) {
            $this->_title->add(__('New Shipment'));

            $comment = $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->renderLayout();
        } else {
            $this->_redirect('*/order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noroute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new \Magento\Framework\Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage = __('You created the shipping label.');

            $this->messageManager->addSuccess(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
            $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
        } catch (\Magento\Framework\Model\Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
            } else {
                $this->messageManager->addError(__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->representJson($responseAjax->toJson());
        } else {
            $this->_redirect('sales/order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    /**
     * Send email with shipment data to customer
     *
     * @return void
     */
    public function emailAction()
    {
        try {
            $shipment = $this->_initShipment();
            if ($shipment) {
                $shipment->sendEmail(true)->setEmailSent(true)->save();
                $historyItem = $this->_objectManager->create(
                    'Magento\Sales\Model\Resource\Order\Status\History\Collection'
                )->getUnnotifiedForInstance(
                    $shipment,
                    \Magento\Sales\Model\Order\Shipment::HISTORY_ENTITY_NAME
                );
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $this->messageManager->addSuccess(__('You sent the shipment.'));
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot send shipment information.'));
        }
        $this->_redirect('*/*/view', array('shipment_id' => $this->getRequest()->getParam('shipment_id')));
    }

    /**
     * Add new tracking number action
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function addTrackAction()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number = $this->getRequest()->getPost('number');
            $title = $this->getRequest()->getPost('title');
            if (empty($carrier)) {
                throw new \Magento\Framework\Model\Exception(__('Please specify a carrier.'));
            }
            if (empty($number)) {
                throw new \Magento\Framework\Model\Exception(__('Please enter a tracking number.'));
            }
            $shipment = $this->_initShipment();
            if ($shipment) {
                $track = $this->_objectManager->create(
                    'Magento\Sales\Model\Order\Shipment\Track'
                )->setNumber(
                    $number
                )->setCarrierCode(
                    $carrier
                )->setTitle(
                    $title
                );
                $shipment->addTrack($track)->save();

                $this->_view->loadLayout();
                $response = $this->_view->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error' => true,
                    'message' => __('Cannot initialize shipment for adding tracking number.')
                );
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot add tracking number.'));
        }
        if (is_array($response)) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Remove tracking number from shipment
     *
     * @return void
     */
    public function removeTrackAction()
    {
        $trackId = $this->getRequest()->getParam('track_id');
        $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->load($trackId);
        if ($track->getId()) {
            try {
                if ($this->_initShipment()) {
                    $track->delete();

                    $this->_view->loadLayout();
                    $response = $this->_view->getLayout()->getBlock('shipment_tracking')->toHtml();
                } else {
                    $response = array(
                        'error' => true,
                        'message' => __('Cannot initialize shipment for delete tracking number.')
                    );
                }
            } catch (\Exception $e) {
                $response = array('error' => true, 'message' => __('Cannot delete tracking number.'));
            }
        } else {
            $response = array('error' => true, 'message' => __('Cannot load track with retrieving identifier.'));
        }
        if (is_array($response)) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Add comment to shipment history
     *
     * @return void
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam('shipment_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Model\Exception(__("The comment text field cannot be empty."));
            }
            $shipment = $this->_initShipment();
            $shipment->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $shipment->save();

            $this->_view->loadLayout(false);
            $response = $this->_view->getLayout()->getBlock('shipment_comments')->toHtml();
        } catch (\Magento\Framework\Model\Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (\Exception $e) {
            $response = array('error' => true, 'message' => __('Cannot add new comment.'));
        }
        if (is_array($response)) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        } else {
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Create shipping label for specific shipment with validation.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @throws \Magento\Framework\Model\Exception
     * @return bool
     */
    protected function _createShippingLabel(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        if (!$shipment) {
            return false;
        }
        $order = $shipment->getOrder();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode());
        if (!$carrier->isShippingLabelsAvailable()) {
            return false;
        }
        $shipment->setPackages($this->getRequest()->getParam('packages'));
        $response = $this->_objectManager->create(
            'Magento\Shipping\Model\Shipping\Labels'
        )->requestToShipment(
            $shipment
        );
        if ($response->hasErrors()) {
            throw new \Magento\Framework\Model\Exception($response->getErrors());
        }
        if (!$response->hasInfo()) {
            return false;
        }
        $labelsContent = array();
        $trackingNumbers = array();
        $info = $response->getInfo();
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }
        $outputPdf = $this->_combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render());
        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = $this->_objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->getValue(
            'carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $shipment->getStoreId()
        );
        if ($trackingNumbers) {
            foreach ($trackingNumbers as $trackingNumber) {
                $track = $this->_objectManager->create(
                    'Magento\Sales\Model\Order\Shipment\Track'
                )->setNumber(
                    $trackingNumber
                )->setCarrierCode(
                    $carrierCode
                )->setTitle(
                    $carrierTitle
                );
                $shipment->addTrack($track);
            }
        }
        return true;
    }

    /**
     * Create shipping label action for specific shipment
     *
     * @return void
     */
    public function createLabelAction()
    {
        $response = new \Magento\Framework\Object();
        try {
            $shipment = $this->_initShipment();
            if ($this->_createShippingLabel($shipment)) {
                $shipment->save();
                $this->messageManager->addSuccess(__('You created the shipping label.'));
                $response->setOk(true);
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $response->setError(true);
            $response->setMessage(__('An error occurred while creating shipping label.'));
        }

        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Print label for one specific shipment
     *
     * @return ResponseInterface|void
     */
    public function printLabelAction()
    {
        try {
            $shipment = $this->_initShipment();
            $labelContent = $shipment->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new \Zend_Pdf();
                    $page = $this->_createPdfPageFromImageString($labelContent);
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
                    \Magento\Framework\App\Filesystem::VAR_DIR,
                    'application/pdf'
                );
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__('An error occurred while creating shipping label.'));
        }
        $this->_redirect(
            'adminhtml/order_shipment/view',
            array('shipment_id' => $this->getRequest()->getParam('shipment_id'))
        );
    }

    /**
     * Create pdf document with information about packages
     *
     * @return ResponseInterface|void
     */
    public function printPackageAction()
    {
        $shipment = $this->_initShipment();

        if ($shipment) {
            $pdf = $this->_objectManager->create('Magento\Shipping\Model\Order\Pdf\Packaging')->getPdf($shipment);
            return $this->_fileFactory->create(
                'packingslip' . $this->_objectManager->get(
                    'Magento\Framework\Stdlib\DateTime\DateTime'
                )->date(
                    'Y-m-d_H-i-s'
                ) . '.pdf',
                $pdf->render(),
                \Magento\Framework\App\Filesystem::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->_forward('noroute');
        }
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @return ResponseInterface|void
     */
    public function massPrintShippingLabelAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('order_ids');
        $createdFromOrders = !empty($ids);
        $shipments = null;
        $labelsContent = array();
        switch ($request->getParam('massaction_prepare_key')) {
            case 'shipment_ids':
                $ids = $request->getParam('shipment_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = $this->_objectManager->create(
                        'Magento\Sales\Model\Resource\Order\Shipment\Collection'
                    )->addFieldToFilter(
                        'entity_id',
                        array('in' => $ids)
                    );
                }
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = $this->_objectManager->create(
                        'Magento\Sales\Model\Resource\Order\Shipment\Collection'
                    )->setOrderFilter(
                        array('in' => $ids)
                    );
                }
                break;
        }

        if ($shipments && $shipments->getSize()) {
            foreach ($shipments as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
            }
        }

        if (!empty($labelsContent)) {
            $outputPdf = $this->_combineLabelsPdf($labelsContent);
            return $this->_fileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                \Magento\Framework\App\Filesystem::VAR_DIR,
                'application/pdf'
            );
        }

        if ($createdFromOrders) {
            $this->messageManager->addError(__('There are no shipping labels related to selected orders.'));
            $this->_redirect('sales/order/index');
        } else {
            $this->messageManager->addError(__('There are no shipping labels related to selected shipments.'));
            $this->_redirect('sales/shipment/index');
        }
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return \Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new \Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = \Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }

    /**
     * Create \Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return \Zend_Pdf_Page|false
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->_objectManager->get(
            'Magento\Framework\App\Filesystem'
        )->getDirectoryWrite(
            \Magento\Framework\App\Filesystem::TMP_DIR
        );
        $directory->create();
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new \Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = $directory->getAbsolutePath('shipping_labels_' . uniqid(\Magento\Framework\Math\Random::getRandomNumber()) . time() . '.png');
        imagepng($image, $tmpFileName);
        $pdfImage = \Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        $directory->delete($directory->getRelativePath($tmpFileName));
        return $page;
    }

    /**
     * Return grid with shipping items for Ajax request
     *
     * @return ResponseInterface
     */
    public function getShippingItemsGridAction()
    {
        $this->_initShipment();
        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid'
            )->setIndex(
                $this->getRequest()->getParam('index')
            )->toHtml()
        );
    }
}
