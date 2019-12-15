<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Helper\Reorder as ReorderHelper;

/**
 * Abstract class for controllers Reorder(Customer) and Reorder(Guest)
 *
 * Class Magento\Sales\Controller\AbstractController\Reorder
 */
abstract class Reorder extends Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ReorderHelper
     */
    private $reorderHelper;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param ReorderHelper|null $reorderHelper
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        ReorderHelper $reorderHelper = null
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        $this->reorderHelper = $reorderHelper ?: ObjectManager::getInstance()->get(ReorderHelper::class);
        parent::__construct($context);
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->_coreRegistry->registry('current_order');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->reorderHelper->canReorder($order->getId())) {
            $this->messageManager->addErrorMessage(__("Reorder is not available."));
            return $resultRedirect->setPath('checkout/cart');
        }

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
