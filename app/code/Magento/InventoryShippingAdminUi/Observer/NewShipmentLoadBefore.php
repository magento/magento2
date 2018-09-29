<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Redirect to source selection page
 */
class NewShipmentLoadBefore implements ObserverInterface
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var IsWebsiteInMultiSourceMode
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @param OrderRepository $orderRepository
     * @param IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
     * @param RedirectInterface $redirect
     */
    public function __construct(
        OrderRepository $orderRepository,
        IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode,
        RedirectInterface $redirect
    ) {
        $this->orderRepository = $orderRepository;
        $this->isWebsiteInMultiSourceMode = $isWebsiteInMultiSourceMode;
        $this->redirect = $redirect;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $controller = $observer->getEvent()->getControllerAction();

        if (!empty($request->getParam('items'))
            && !empty($request->getParam('sourceCode'))) {
            return;
        }

        try {
            $orderId = $request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            $websiteId = (int)$order->getStore()->getWebsiteId();
            if ($this->isWebsiteInMultiSourceMode->execute($websiteId)) {
                $this->redirect->redirect(
                    $controller->getResponse(),
                    'inventoryshipping/SourceSelection/index',
                    [
                        'order_id' => $orderId
                    ]
                );
            }
        } catch (InputException | NoSuchEntityException $e) {
            $this->redirect->redirect(
                $controller->getResponse(),
                'sales/order/index'
            );
        }

        return;
    }
}
