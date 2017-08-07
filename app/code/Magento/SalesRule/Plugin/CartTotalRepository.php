<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Plugin;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartTotalRepository
 * @package Magento\SalesRule\Plugin
 * @since 2.2.0
 */
class CartTotalRepository
{
    /**
     * @var \Magento\Quote\Api\Data\TotalsExtensionFactory
     * @since 2.2.0
     */
    private $extensionFactory;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     * @since 2.2.0
     */
    private $ruleRepository;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     * @since 2.2.0
     */
    private $coupon;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * CartTotalRepository constructor.
     * @param \Magento\Quote\Api\Data\TotalsExtensionFactory $extensionFactory
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param StoreManagerInterface $storeManager
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Quote\Api\Data\TotalsExtensionFactory $extensionFactory,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\SalesRule\Model\Coupon $coupon,
        StoreManagerInterface $storeManager
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->ruleRepository = $ruleRepository;
        $this->coupon = $coupon;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $result
     * @return \Magento\Quote\Api\Data\TotalsInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGet(
        \Magento\Quote\Model\Cart\CartTotalRepository $subject,
        \Magento\Quote\Api\Data\TotalsInterface $result
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

        /* @var $label \Magento\SalesRule\Model\Data\RuleLabel */
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
