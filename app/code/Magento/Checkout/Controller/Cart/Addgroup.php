<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Item;

/**
 * Add grouped items controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Addgroup extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param Escaper|null $escaper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ?Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * Add items in group.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderItemIds = $this->getRequest()->getPost('order_items');
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class)
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection \Magento\Sales\Model\ResourceModel\Order\Item\Collection */
            foreach ($itemsCollection as $item) {
                try {
                    $this->addOrderItem($item);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNoticeMessage($e->getMessage());
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    return $this->_goBack();
                }
            }
            $this->cart->save();
        } else {
            $this->messageManager->addErrorMessage(__('Please select at least one product to add to cart'));
        }
        return $this->_goBack();
    }

    /**
     * Add item to cart.
     *
     * Add item to cart only if it's belongs to customer.
     *
     * @param Item $item
     * @return void
     */
    private function addOrderItem(Item $item)
    {
        /** @var \Magento\Customer\Model\Session $session */
        $session = $this->cart->getCustomerSession();
        if ($session->isLoggedIn()) {
            $orderCustomerId = $item->getOrder()->getCustomerId();
            $currentCustomerId = $session->getCustomer()->getId();
            if ($orderCustomerId == $currentCustomerId) {
                $this->cart->addOrderItem($item, 1);
                if (!$this->cart->getQuote()->getHasError()) {
                    $this->messageManager->addComplexSuccessMessage(
                        'addCartSuccessMessage',
                        [
                            'product_name' => $item->getName(),
                            'cart_url' => $this->getCartUrl()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
