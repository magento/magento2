<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\Registry;

abstract class Reorder extends Action\Action
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
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
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

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                    $this->messageManager->addNotice($e->getMessage());
                } else {
                    $this->messageManager->addError($e->getMessage());
                }
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
