<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\Model\Session;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceEvent;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Load Creditmemo
 *
 * @method CreditmemoLoader setCreditmemoId($id)
 * @method CreditmemoLoader setCreditmemo($creditMemo)
 * @method CreditmemoLoader setInvoiceId($id)
 * @method CreditmemoLoader setOrderId($id)
 * @method int getCreditmemoId()
 * @method string getCreditmemo()
 * @method int getInvoiceId()
 * @method int getOrderId()
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CreditmemoLoader extends DataObject
{
    /**
     * @var CreditmemoRepositoryInterface;
     */
    protected $creditmemoRepository;

    /**
     * @var CreditmemoFactory;
     */
    protected $creditmemoFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var ManagerInterfaceEvent
     */
    protected $eventManager;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param OrderFactory $orderFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ManagerInterfaceEvent $eventManager
     * @param Session $backendSession
     * @param ManagerInterface $messageManager
     * @param Registry $registry
     * @param StockConfigurationInterface $stockConfiguration
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        OrderFactory $orderFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        ManagerInterfaceEvent $eventManager,
        Session $backendSession,
        ManagerInterface $messageManager,
        Registry $registry,
        StockConfigurationInterface $stockConfiguration,
        array $data = []
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->orderFactory = $orderFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->eventManager = $eventManager;
        $this->backendSession = $backendSession;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct($data);
    }

    /**
     * Get requested items qtys and return to stock flags
     *
     * @return array
     */
    protected function _getItemData()
    {
        $data = $this->getCreditmemo();
        if (!$data) {
            $data = $this->backendSession->getFormData(true);
        }

        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }

    /**
     * Check if creditmeno can be created for order
     *
     * @param Order $order
     * @return bool
     */
    protected function _canCreditmemo($order)
    {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            $this->messageManager->addErrorMessage(__('We can\'t create credit memo for the order.'));
            return false;
        }
        return true;
    }

    /**
     * Initialize invoice
     *
     * @param Order $order
     * @return $this|bool
     */
    protected function _initInvoice($order)
    {
        $invoiceId = $this->getInvoiceId();
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @return Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function load()
    {
        $creditmemo = false;
        $creditmemoId = $this->getCreditmemoId();
        $orderId = $this->getOrderId();
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getCreditmemo();
            $order = $this->orderFactory->create()->load($orderId);
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($order)) {
                return false;
            }

            $savedData = $this->_getItemData();

            $qtys = [];
            $backToStock = [];
            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            if ($invoice) {
                $creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $data);
            } else {
                $creditmemo = $this->creditmemoFactory->createByOrder($order, $data);
            }

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if ($parentId && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(
                        $this->stockConfiguration->isAutoReturnEnabled()
                    );
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $this->eventManager->dispatch(
            'adminhtml_sales_order_creditmemo_register_before',
            ['creditmemo' => $creditmemo, 'input' => $this->getCreditmemo()]
        );

        $this->registry->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }
}
