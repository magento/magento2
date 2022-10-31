<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Layout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Depersonalize customer data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var int
     */
    private $quoteId;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param CheckoutSession $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        CheckoutSession $checkoutSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Resolve quote data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function beforeGenerateXml(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->quoteId = $this->checkoutSession->getQuoteId();
        }
    }

    /**
     * Change sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->checkoutSession->clearStorage();
            $this->checkoutSession->setQuoteId($this->quoteId);
        }
    }
}
