<?php
declare(strict_types=1);

namespace Magento\Paypal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\Generic;

class OnCheckoutSuccessClearQuote implements ObserverInterface
{
    /**
     * @var Generic
     */
    private Generic $checkoutSession;

    /**
     * OnCheckoutSuccessClearQuote constructor.
     * @param Generic $checkoutSession
     */
    public function __construct(Generic $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Regularly magento uses checkout session. For paypal checkout it uses custom Generic session.
     * <virtualType name="Magento\Paypal\Model\Session" type="Magento\Framework\Session\Generic">
     * On success this session also need to be cleared.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $this->checkoutSession->clearStorage();
    }
}
