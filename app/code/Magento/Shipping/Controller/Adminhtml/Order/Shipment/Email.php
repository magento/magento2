<?php
/**
 *
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
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use \Magento\Backend\App\Action;
use \Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

class Email extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param ShipmentSender $shipmentSender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        ShipmentSender $shipmentSender
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::shipment');
    }

    /**
     * Send email with shipment data to customer
     *
     * @return void
     */
    public function execute()
    {
        try {
            $shipment = $this->shipmentLoader->load($this->_request);
            if ($shipment) {
                $this->shipmentSender->send($shipment, true);
                $shipment->save();

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
}
