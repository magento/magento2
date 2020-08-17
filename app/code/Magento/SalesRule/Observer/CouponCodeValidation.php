<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;

/**
 * Validate newly provided coupon code before using it while calculating totals.
 */
class CouponCodeValidation implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CodeLimitManagerInterface
     */
    private $codeLimitManager;

    /**
     * @param CodeLimitManagerInterface $codeLimitManager
     * @param CartRepositoryInterface $cartRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        CodeLimitManagerInterface $codeLimitManager,
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->codeLimitManager = $codeLimitManager;
        $this->cartRepository = $cartRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        $code = $quote->getCouponCode();
        if ($code) {
            //Only validating the code if it's a new code.
            /** @var Quote[] $found */
            $found = $this->cartRepository->getList(
                $this->criteriaBuilder->addFilter('main_table.' . CartInterface::KEY_ENTITY_ID, $quote->getId())
                    ->create()
            )->getItems();
            if (!$found || ((string)array_shift($found)->getCouponCode()) !== (string)$code) {
                try {
                    $this->codeLimitManager->checkRequest($code);
                } catch (CodeRequestLimitException $exception) {
                    $quote->setCouponCode('');
                    throw $exception;
                }
            }
        }
    }
}
