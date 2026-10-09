<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Backend\Model\Session\Quote as AdminSessionQuote;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Calculates usage-limit offset when editing an existing admin order.
 *
 * The original order consumes a usage slot; it is canceled after the edited order is placed,
 * so that consumption must not block re-applying the same rule during edit.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class OrderEditUsageOffset
{
    /**
     * @param State $appState
     * @param AdminSessionQuote $adminSessionQuote
     */
    public function __construct(
        private readonly State $appState,
        private readonly AdminSessionQuote $adminSessionQuote
    ) {
    }

    /**
     * Return how many times a rule was used by the order being edited.
     *
     * @param Address $address
     * @param int $ruleId
     * @return int
     */
    public function getOffset(Address $address, int $ruleId): int
    {
        return $this->getOffsetForQuote($address->getQuote(), $ruleId);
    }

    /**
     * Return how many times a rule was used by the order being edited.
     *
     * @param CartInterface $quote
     * @param int $ruleId
     * @return int
     */
    public function getOffsetForQuote(CartInterface $quote, int $ruleId): int
    {
        return $this->getOffsetForRuleId($ruleId, $quote);
    }

    /**
     * Return how many times a rule was used by the order being edited.
     *
     * Uses admin session when quote edit-context data is unavailable (e.g. Multicoupon validation without quote).
     *
     * @param int $ruleId
     * @param CartInterface|null $quote
     * @return int
     */
    public function getOffsetForRuleId(int $ruleId, ?CartInterface $quote = null): int
    {
        if (!$this->isAdminOrderEdit()) {
            return 0;
        }
        $originalRuleIds = $this->getOriginalAppliedRuleIds($quote);
        if (!$originalRuleIds) {
            return 0;
        }
        return in_array((string)$ruleId, explode(',', $originalRuleIds), true) ? 1 : 0;
    }

    /**
     * Resolve applied rule IDs from the order being edited.
     *
     * @param CartInterface|null $quote
     * @return string|null
     */
    private function getOriginalAppliedRuleIds(?CartInterface $quote): ?string
    {
        if ($quote !== null) {
            $ruleIds = $quote->getData(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS);
            if ($ruleIds) {
                return (string)$ruleIds;
            }
        }

        if (!$this->adminSessionQuote->getData('order_id') || $this->adminSessionQuote->getData('reordered')) {
            return null;
        }

        $order = $this->adminSessionQuote->getOrder();
        if (!$order->getId()) {
            return null;
        }

        $appliedRuleIds = $order->getAppliedRuleIds();
        return $appliedRuleIds ? (string)$appliedRuleIds : null;
    }

    /**
     * Whether current request is editing an existing admin order.
     *
     * @return bool
     */
    private function isAdminOrderEdit(): bool
    {
        try {
            return $this->appState->getAreaCode() === Area::AREA_ADMINHTML;
        } catch (LocalizedException $exception) {
            return false;
        }
    }
}
