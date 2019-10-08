<?php

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Quote\Api\CartRepositoryInterface;
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
     * @var Iterator
     */
    private $iterator;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * UpdateQuoteOnDisableSalesrule constructor.
     * @param Iterator $iterator
     * @param CollectionFactory $collectionFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Iterator $iterator,
        CollectionFactory $collectionFactory,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        $this->iterator = $iterator;
        $this->collectionFactory = $collectionFactory;
        $this->cartRepository = $cartRepository;
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
        $this->iterateQuotes($rule);
    }

    /**
     * To prevent out of memory issue we read row by row.
     *
     * @param Rule $rule
     * @return void
     */
    private function iterateQuotes(Rule $rule): void
    {
        /** @var Collection $quoteCollection */
        $quoteCollection = $this->collectionFactory->create();
        $quoteCollection->getSelect()
            ->where('is_active = 1')
            ->where('FIND_IN_SET(?, applied_rule_ids)', $rule->getId());
        $this->iterator->walk($quoteCollection->getSelect(), [[$this, 'callbackRecalculateQuote']]);
    }

    /**
     * Callback to get and recalculate quote
     *
     * @param $args
     * @return void
     */
    public function callbackRecalculateQuote($args): void
    {
        try {
            /** @var Quote $quote */
            $quote = $this->cartRepository->get($args['row']['entity_id']);
            $quote->setAppliedRuleIds('');
            $quote->collectTotals()->save();
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
