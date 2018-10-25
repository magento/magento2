<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NotFoundException;

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
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param Validator|null $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        Validator $formKeyValidator = null
    ) {
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->create(Validator::class);
        parent::__construct($context);
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            throw new NotFoundException(__('Page not found.'));
            return;
        }

        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->_coreRegistry->registry('current_order');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
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
