<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Plugin;

use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\CartTotalRepository as CartTotalRepositoryOrg;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Data\RuleLabel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartTotalRepository
 * @package Magento\SalesRule\Plugin
 */
class CartTotalRepository
{
    /**
     * CartTotalRepository constructor.
     * @param TotalsExtensionFactory $extensionFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param Coupon $coupon
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly TotalsExtensionFactory $extensionFactory,
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly Coupon $coupon,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param CartTotalRepositoryOrg $subject
     * @param TotalsInterface $result
     * @return TotalsInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CartTotalRepositoryOrg $subject,
        TotalsInterface $result
    ) {
        if ($result->getExtensionAttributes() === null) {
            $extensionAttributes = $this->extensionFactory->create();
            $result->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes = $result->getExtensionAttributes();
        $couponCode = $result->getCouponCode();

        if (empty($couponCode)) {
            return $result;
        }

        $this->coupon->loadByCode($couponCode);
        $ruleId = $this->coupon->getRuleId();

        if (empty($ruleId)) {
            return $result;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $rule = $this->ruleRepository->getById($ruleId);

        $storeLabel = $storeLabelFallback = null;

        /* @var RuleLabel $label */
        foreach ($rule->getStoreLabels() as $label) {
            if ($label->getStoreId() === 0) {
                $storeLabelFallback = $label->getStoreLabel();
            }

            if ($label->getStoreId() == $storeId) {
                $storeLabel = $label->getStoreLabel();
                break;
            }
        }

        $extensionAttributes->setCouponLabel(($storeLabel) ? $storeLabel : $storeLabelFallback);
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }
}
