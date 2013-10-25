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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order shipment controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Sales\Order;

class Shipment extends \Magento\Adminhtml\Controller\Sales\Shipment\AbstractShipment
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize shipment items QTY
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
     * @return \Magento\Sales\Model\Order\Shipment|bool
     */
    protected function _initShipment()
    {
        $this->_title(__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->_getSession()->addError(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError(__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Service\Order', array('order' => $order))
                ->prepareShipment($savedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        throw new \Magento\Core\Exception(__('Please enter a tracking number.'));
                    }
                    $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                        ->addData($data);
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
     * @return \Magento\Adminhtml\Controller\Sales\Order\Shipment
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = $this->_objectManager->create('Magento\Core\Model\Resource\Transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    /**
     * Shipment information page
     */
    public function viewAction()
    {
        $shipment = $this->_initShipment();
        if ($shipment) {
            $this->_title("#" . $shipment->getIncrementId());
            $this->loadLayout();
            $this->getLayout()->getBlock('sales_shipment_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('Magento_Sales::sales_order')
                ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Start create shipment action
     */
    public function startAction()
    {
        /**
         * Clear old values for shipment qty's
         */
        $this->_redirect('*/*/new', array('order_id'=>$this->getRequest()->getParam('order_id')));
    }

    /**
     * Shipment create page
     */
    public function newAction()
    {
        $shipment = $this->_initShipment();
        if ($shipment) {
            $this->_title(__('New Shipment'));

            $comment = $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }

            $this->loadLayout()
                ->_setActiveMenu('Magento_Sales::sales_order')
                ->renderLayout();
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
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
            $responseAjax = new \Magento\Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage    = __('You created the shipping label.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getCommentText(true);
        } catch (\Magento\Core\Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    __('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError(__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    /**
     * Send email with shipment data to customer
     */
    public function emailAction()
    {
        try {
            $shipment = $this->_initShipment();
            if ($shipment) {
                $shipment->sendEmail(true)
                    ->setEmailSent(true)
                    ->save();
                $historyItem = $this->_objectManager->create('Magento\Sales\Model\Resource\Order\Status\History\Collection')
                    ->getUnnotifiedForInstance($shipment, \Magento\Sales\Model\Order\Shipment::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $this->_getSession()->addSuccess(__('You sent the shipment.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addError(__('Cannot send shipment information.'));
        }
        $this->_redirect('*/*/view', array(
            'shipment_id' => $this->getRequest()->getParam('shipment_id')
        ));
    }

    /**
     * Add new tracking number action
     */
    public function addTrackAction()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            if (empty($carrier)) {
                throw new \Magento\Core\Exception(__('Please specify a carrier.'));
            }
            if (empty($number)) {
                throw new \Magento\Core\Exception(__('Please enter a tracking number.'));
            }
            $shipment = $this->_initShipment();
            if ($shipment) {
                $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                    ->setNumber($number)
                    ->setCarrierCode($carrier)
                    ->setTitle($title);
                $shipment->addTrack($track)
                    ->save();

                $this->loadLayout();
                $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => __('Cannot initialize shipment for adding tracking number.'),
                );
            }
        } catch (\Magento\Core\Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (\Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => __('Cannot add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Remove tracking number from shipment
     */
    public function removeTrackAction()
    {
        $trackId    = $this->getRequest()->getParam('track_id');
        $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->load($trackId);
        if ($track->getId()) {
            try {
                if ($this->_initShipment()) {
                    $track->delete();

                    $this->loadLayout();
                    $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
                } else {
                    $response = array(
                        'error'     => true,
                        'message'   => __('Cannot initialize shipment for delete tracking number.'),
                    );
                }
            } catch (\Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => __('Cannot delete tracking number.'),
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => __('Cannot load track with retrieving identifier.'),
            );
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * View shipment tracking information
     */
    public function viewTrackAction()
    {
        $trackId    = $this->getRequest()->getParam('track_id');
        $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->load($trackId);
        if ($track->getId()) {
            try {
                $response = $track->getNumberDetail();
            } catch (\Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => __('Cannot retrieve tracking number detail.'),
                );
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => __('Cannot load track with retrieving identifier.'),
            );
        }

        if (is_object($response)) {
            $block = $this->_objectManager->create('Magento\Adminhtml\Block\Template');
            $block->setTemplate('sales/order/shipment/tracking/info.phtml');
            $block->setTrackingInfo($response);

            $this->getResponse()->setBody($block->toHtml());
        } else {
            if (is_array($response)) {
                $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
            }

            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Add comment to shipment history
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam(
                'shipment_id',
                $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Core\Exception(__("The comment text field cannot be empty."));
            }
            $shipment = $this->_initShipment();
            $shipment->addComment(
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $shipment->save();

            $this->loadLayout(false);
            $response = $this->getLayout()->getBlock('shipment_comments')->toHtml();
        } catch (\Magento\Core\Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
        } catch (\Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => __('Cannot add new comment.')
            );
            $response = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Create shipping label for specific shipment with validation.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return bool
     */
    protected function _createShippingLabel(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        if (!$shipment) {
            return false;
        }
        $carrier = $shipment->getOrder()->getShippingCarrier();
        if (!$carrier->isShippingLabelsAvailable()) {
            return false;
        }
        $shipment->setPackages($this->getRequest()->getParam('packages'));
        $response = $this->_objectManager->create('Magento\Shipping\Model\Shipping')->requestToShipment($shipment);
        if ($response->hasErrors()) {
            throw new \Magento\Core\Exception($response->getErrors());
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
        $carrierTitle = $this->_objectManager->get('Magento\Core\Model\Store\Config')
            ->getConfig('carriers/'.$carrierCode.'/title', $shipment->getStoreId());
        if ($trackingNumbers) {
            foreach ($trackingNumbers as $trackingNumber) {
                $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                        ->setNumber($trackingNumber)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle);
                $shipment->addTrack($track);
            }
        }
        return true;
    }

    /**
     * Create shipping label action for specific shipment
     *
     */
    public function createLabelAction()
    {
        $response = new \Magento\Object();
        try {
            $shipment = $this->_initShipment();
            if ($this->_createShippingLabel($shipment)) {
                $shipment->save();
                $this->_getSession()->addSuccess(__('You created the shipping label.'));
                $response->setOk(true);
            }
        } catch (\Magento\Core\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $response->setError(true);
            $response->setMessage(__('An error occurred while creating shipping label.'));
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Print label for one specific shipment
     *
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
                        $this->_getSession()
                            ->addError(__('We don\'t recognize or support the file extension in this shipment: %1.',
                                $shipment->getIncrementId()));
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_prepareDownloadResponse(
                    'ShippingLabel(' . $shipment->getIncrementId() . ').pdf',
                    $pdfContent,
                    'application/pdf'
                );
            }
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()
                ->addError(__('An error occurred while creating shipping label.'));
       }
       $this->_redirect('*/sales_order_shipment/view', array(
           'shipment_id' => $this->getRequest()->getParam('shipment_id')
       ));
    }

    /**
     * Create pdf document with information about packages
     *
     * @return void
     */
    public function printPackageAction()
    {
        $shipment = $this->_initShipment();

        if ($shipment) {
            $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment\Packaging')->getPdf($shipment);
            $this->_prepareDownloadResponse(
                'packingslip' . $this->_objectManager->get('Magento\Core\Model\Date')->date('Y-m-d_H-i-s') . '.pdf',
                $pdf->render(),
                'application/pdf'
            );
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @return null
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
                    $shipments = $this->_objectManager->create('Magento\Sales\Model\Resource\Order\Shipment\Collection')
                        ->addFieldToFilter('entity_id', array('in' => $ids));
                }
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = $this->_objectManager->create('Magento\Sales\Model\Resource\Order\Shipment\Collection')
                        ->setOrderFilter(array('in' => $ids));
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
            $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf->render(), 'application/pdf');
            return;
        }

        if ($createdFromOrders) {
            $this->_getSession()
                ->addError(__('There are no shipping labels related to selected orders.'));
            $this->_redirect('*/sales_order/index');
        } else {
            $this->_getSession()
                ->addError(__('There are no shipping labels related to selected shipments.'));
            $this->_redirect('*/sales_order_shipment/index');
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
     * @return \Zend_Pdf_Page|bool
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        /** @var \Magento\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get('Magento\Filesystem');
        /** @var $tmpDir \Magento\App\Dir */
        $tmpDir = $this->_objectManager->get('Magento\App\Dir', $filesystem->getWorkingDirectory());
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new \Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = $tmpDir->getDir(\Magento\App\Dir::TMP) . 'shipping_labels_'
                     . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = \Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        $filesystem->delete($tmpFileName);
        return $page;
    }

    /**
     * Return grid with shipping items for Ajax request
     *
     * @return \Magento\App\ResponseInterface
     */
    public function getShippingItemsGridAction()
    {
        $this->_initShipment();
        return $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Magento\Adminhtml\Block\Sales\Order\Shipment\Packaging\Grid')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
       );
    }
}
