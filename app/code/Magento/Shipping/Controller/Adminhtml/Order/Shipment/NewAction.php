<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\ShipmentProviderInterface;

class NewAction extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::ship';

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentProviderInterface
     */
    private $shipmentProvider;

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param ShipmentLoader $shipmentLoader
     * @param Redirect|null $redirect
     * @param PageFactory|null $resultPageFactory
     * @param ShipmentProviderInterface|null $shipmentProvider
     */
    public function __construct(
        Action\Context $context,
        ShipmentLoader $shipmentLoader,
        Redirect $redirect = null,
        PageFactory $resultPageFactory = null,
        ShipmentProviderInterface $shipmentProvider = null
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->redirect = $redirect ?: ObjectManager::getInstance()
            ->get(Redirect::class);
        $this->resultPageFactory = $resultPageFactory ?: ObjectManager::getInstance()
            ->get(PageFactory::class);
        $this->shipmentProvider = $shipmentProvider ?: ObjectManager::getInstance()
            ->get(ShipmentProviderInterface::class);
        parent::__construct($context);
    }

    /**
     * Shipment create page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->shipmentProvider->getShipmentData());
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
        $shipment = $this->shipmentLoader->load();
        if ($shipment) {
            $comment = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }

            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Magento_Sales::sales_order');
            $resultPage->getConfig()->getTitle()->prepend(__('Shipments'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Shipment'));
            return $resultPage;
        } else {
            return $this->redirect->setPath('*/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }
    }
}
