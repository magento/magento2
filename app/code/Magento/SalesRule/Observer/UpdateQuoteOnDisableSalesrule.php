<?php

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\SalesRule\Model\Rule;

class UpdateQuoteOnDisableSalesrule implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * UpdateQuoteOnDisableSalesrule constructor.
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceConnection $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * We should ignore active rules and add flag to recalculate quotes only when rule is disabled.
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
        $quoteCollection->getSelect()
            ->where('is_active = 1')
            ->where('FIND_IN_SET(?, applied_rule_ids)', $rule->getId());
        $this->resource->getConnection()->update(
            $this->resource->getTableName('quote'),
            ['trigger_recollect' => 1],
            ['entity_id IN (?)' => $quoteCollection->getAllIds()]
        );
    }
}
