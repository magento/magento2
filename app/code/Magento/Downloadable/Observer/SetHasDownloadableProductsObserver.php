<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Downloadable\Observer\SetHasDownloadableProductsObserver
 *
 * @since 2.0.0
 */
class SetHasDownloadableProductsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Set checkout session flag if order has downloadable product(s)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_checkoutSession->getHasDownloadableProducts()) {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getAllItems() as $item) {
                /* @var $item \Magento\Sales\Model\Order\Item */
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                    || $item->getProductOptionByCode(
                        'is_downloadable'
                    )
                ) {
                    $this->_checkoutSession->setHasDownloadableProducts(true);
                    break;
                }
            }
        }

        return $this;
    }
}
