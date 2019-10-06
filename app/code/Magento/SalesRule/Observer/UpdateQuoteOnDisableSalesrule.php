<?php

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Psr\Log\LoggerInterface;

class UpdateQuoteOnDisableSalesrule implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateQuoteOnDisableSalesrule constructor.
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(CollectionFactory $collectionFactory, LoggerInterface $logger)
    {
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * We should ignore active rules and recalculate quotes only when rule is disabled.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Rule $rule */
        $rule = $observer->getRule();
        if ($rule->getIsActive()) {
            return;
        }
        /** @var Collection $quoteCollection */
        $quoteCollection = $this->collectionFactory->create();
        $quoteCollection->getSelect()->where('FIND_IN_SET(?, applied_rule_ids)', $rule->getId());
        /** @var Quote $quote */
        foreach ($quoteCollection as $quote) {
            try {
                $quote->collectTotals()->save();
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }
}