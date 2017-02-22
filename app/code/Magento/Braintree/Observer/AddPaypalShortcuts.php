<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Braintree\Block\PayPal\Shortcut;

class AddPaypalShortcuts implements ObserverInterface
{
    const PAYPAL_SHORTCUT_BLOCK = 'Magento\Braintree\Block\PayPal\Shortcut';

    /**
     * @var \Magento\Braintree\Model\Config\PayPal
     */
    protected $paypalConfig;

    /**
     * @var \Magento\Braintree\Model\PaymentMethod\PayPal
     */
    protected $methodPayPal;

    /**
     * @param \Magento\Braintree\Model\PaymentMethod\PayPal $methodPayPal
     * @param \Magento\Braintree\Model\Config\PayPal $paypalConfig
     */
    public function __construct(
        \Magento\Braintree\Model\PaymentMethod\PayPal $methodPayPal,
        \Magento\Braintree\Model\Config\PayPal $paypalConfig
    ) {
        $this->methodPayPal = $methodPayPal;
        $this->paypalConfig = $paypalConfig;
    }

    /**
     * Add Braintree PayPal shortcut buttons
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMiniCart = !$observer->getEvent()->getIsCatalogProduct();

        //Don't display shortcut on product view page
        if (!$this->methodPayPal->isActive() ||
            !$this->paypalConfig->isShortcutCheckoutEnabled() ||
            !$isMiniCart) {
            return;
        }

        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        /** @var Shortcut $shortcut */
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            self::PAYPAL_SHORTCUT_BLOCK,
            '',
            [
                'data' => [
                    Shortcut::MINI_CART_FLAG_KEY => $isMiniCart
                ]
            ]
        );

        if ($shortcut->skipShortcutForGuest()) {
            return;
        }
        $shortcut->setShowOrPosition($observer->getEvent()->getOrPosition());
        $shortcutButtons->addShortcut($shortcut);
    }
}
