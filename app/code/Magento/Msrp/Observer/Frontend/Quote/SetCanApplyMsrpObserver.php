<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Observer\Frontend\Quote;

use Magento\Msrp\Model\Config;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetCanApplyMsrp
 */
class SetCanApplyMsrpObserver implements ObserverInterface
{
    /** @var Config */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Set Quote information about MSRP price enabled
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if ($this->config->isEnabled()) {
            foreach ($quote->getAllAddresses() as $address) {
                if ($address->getCanApplyMsrp()) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }

        $quote->setCanApplyMsrp($canApplyMsrp);
    }
}
