<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\SourceSelection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Index | Need to rebuild
 */
class Index extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param OrderRepository $orderRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            $this->registry->register('current_order', $order);
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Source Selection'));
            $this->_view->renderLayout();
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect($this->_redirect->getRefererUrl());
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}
