<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action;

abstract class Reorder extends Action\Action
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Action for reorder
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }
        $order = $this->_coreRegistry->registry('current_order');

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Model\Exception $e) {
                if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                    $this->messageManager->addNotice($e->getMessage());
                } else {
                    $this->messageManager->addError($e->getMessage());
                }
                $this->_redirect('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We cannot add this item to your shopping cart.'));
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');
    }
}
