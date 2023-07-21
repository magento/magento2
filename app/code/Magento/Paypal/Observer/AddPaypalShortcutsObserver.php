<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Paypal\Helper\Shortcut\Factory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Paypal\Block\Express\InContext\Minicart\SmartButton as MinicartSmartButton;
use Magento\Paypal\Block\Express\InContext\SmartButton as SmartButton;

/**
 * PayPal module observer
 */
class AddPaypalShortcutsObserver implements ObserverInterface
{
    /**
     * @var Factory
     */
    protected $shortcutFactory;

    /**
     * @var PaypalConfig
     */
    protected $paypalConfig;

    /**
     * Constructor
     *
     * @param Factory $shortcutFactory
     * @param PaypalConfig $paypalConfig
     */
    public function __construct(
        Factory $shortcutFactory,
        PaypalConfig $paypalConfig
    ) {
        $this->shortcutFactory = $shortcutFactory;
        $this->paypalConfig = $paypalConfig;
    }

    /**
     * Add PayPal shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks = [
            MinicartSmartButton::class =>
                PaypalConfig::METHOD_WPS_EXPRESS,
            SmartButton::class => PaypalConfig::METHOD_WPS_EXPRESS,
            \Magento\Paypal\Block\Express\Shortcut::class => PaypalConfig::METHOD_WPP_EXPRESS,
            \Magento\Paypal\Block\Bml\Shortcut::class => PaypalConfig::METHOD_WPP_EXPRESS,
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            \Magento\Paypal\Block\WpsExpress\Shortcut::class => PaypalConfig::METHOD_WPS_EXPRESS,
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            \Magento\Paypal\Block\WpsBml\Shortcut::class => PaypalConfig::METHOD_WPS_EXPRESS,
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            \Magento\Paypal\Block\PayflowExpress\Shortcut::class => PaypalConfig::METHOD_WPP_PE_EXPRESS,
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            \Magento\Paypal\Block\Payflow\Bml\Shortcut::class => PaypalConfig::METHOD_WPP_PE_EXPRESS
        ];
        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->paypalConfig->isMethodAvailable($paymentMethodCode)) {
                continue;
            }

            $params = [
                'shortcutValidator' => $this->shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
            ];
            if (!in_array('Bml', explode('\\', $blockInstanceName))) {
                $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();
            }

            // we believe it's \Magento\Framework\View\Element\Template
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                $blockInstanceName,
                '',
                $params
            );
            $shortcut->setIsInCatalogProduct($observer->getEvent()->getIsCatalogProduct())
                ->setShowOrPosition($observer->getEvent()->getOrPosition())
                ->setIsShoppingCart((bool) $observer->getEvent()->getIsShoppingCart());
            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
