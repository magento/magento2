<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Observer;

use Magento\Framework\Event\Observer;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddPaypalShortcuts
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class AddPaypalShortcuts implements ObserverInterface
{
    /**
     * Alias for mini-cart block.
     */
    private const PAYPAL_MINICART_ALIAS = 'mini_cart';

    /**
     * Alias for shopping cart page.
     */
    private const PAYPAL_SHOPPINGCART_ALIAS = 'shopping_cart';

    /**
     * @var string[]
     */
    private $buttonBlocks;

    /**
     * @param string[] $buttonBlocks
     */
    public function __construct(array $buttonBlocks = [])
    {
        $this->buttonBlocks = $buttonBlocks;
    }

    /**
     * Add Braintree PayPal shortcut buttons
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($observer->getData('is_shopping_cart')) {
            $shortcut = $shortcutButtons->getLayout()
                ->createBlock($this->buttonBlocks[self::PAYPAL_SHOPPINGCART_ALIAS]);
        } else {
            $shortcut = $shortcutButtons->getLayout()
                ->createBlock($this->buttonBlocks[self::PAYPAL_MINICART_ALIAS]);
        }

        $shortcutButtons->addShortcut($shortcut);
    }
}
