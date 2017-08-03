<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Observer\Frontend\Quote;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetCanApplyMsrp
 * @since 2.0.0
 */
class SetCanApplyMsrpObserver implements ObserverInterface
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Msrp\Model\Quote\Address\CanApplyMsrp
     * @since 2.0.0
     */
    protected $canApplyMsrp;

    /**
     * @var \Magento\Msrp\Model\Quote\Msrp
     * @since 2.0.0
     */
    protected $msrp;

    /**
     * @param \Magento\Msrp\Model\Config $config
     * @param \Magento\Msrp\Model\Quote\Address\CanApplyMsrp $canApplyMsrp
     * @param \Magento\Msrp\Model\Quote\Msrp $msrp
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Msrp\Model\Config $config,
        \Magento\Msrp\Model\Quote\Address\CanApplyMsrp $canApplyMsrp,
        \Magento\Msrp\Model\Quote\Msrp $msrp
    ) {
        $this->config = $config;
        $this->canApplyMsrp = $canApplyMsrp;
        $this->msrp = $msrp;
    }

    /**
     * Set Quote information about MSRP price enabled
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if ($this->config->isEnabled()) {
            foreach ($quote->getAllAddresses() as $address) {
                if ($this->canApplyMsrp->isCanApplyMsrp($address)) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }
        $this->msrp->setCanApplyMsrp($quote->getId(), $canApplyMsrp);
    }
}
