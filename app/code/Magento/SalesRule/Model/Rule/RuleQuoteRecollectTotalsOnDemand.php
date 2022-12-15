<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule;

use Magento\Framework\DB\Select;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;

/**
 * Forces related quotes to be recollected on demand.
 */
class RuleQuoteRecollectTotalsOnDemand implements RuleQuoteRecollectTotalsInterface
{
    /**
     * Select queries batch size
     */
    private const SELECT_BATCH_SIZE = 10000;

    /**
     * Update queries batch size
     */
    private const UPDATE_BATCH_SIZE = 1000;

    /**
     * @var Quote
     */
    private $quoteResourceModel;

    /**
     * Initializes dependencies
     *
     * @param Quote $quoteResourceModel
     */
    public function __construct(Quote $quoteResourceModel)
    {
        $this->quoteResourceModel = $quoteResourceModel;
    }

    /**
     * Set "trigger_recollect" flag for active quotes which the given rule is applied to.
     *
     * @param int $ruleId
     * @return void
     */
    public function execute(int $ruleId): void
    {
        $connection = $this->quoteResourceModel->getConnection();

        $lastEntityId = 0;
        do {
            $select = $connection->select()
                ->from($this->quoteResourceModel->getMainTable(), ['entity_id'])
                ->where('is_active = ?', 1)
                ->where('FIND_IN_SET(?, applied_rule_ids)', $ruleId)
                ->where('entity_id > ?', (int)$lastEntityId)
                ->order('entity_id ' . Select::SQL_ASC)
                ->limit(self::SELECT_BATCH_SIZE);
            $entityIds = $connection->fetchCol($select);
            $lastEntityId = null;
            if ($entityIds) {
                $lastEntityId = $entityIds[self::SELECT_BATCH_SIZE - 1] ?? null;
                foreach (array_chunk($entityIds, self::UPDATE_BATCH_SIZE) as $batchEntityIds) {
                    $connection->update(
                        $this->quoteResourceModel->getMainTable(),
                        ['trigger_recollect' => 1],
                        [
                            'entity_id IN (?)' => array_map('intval', $batchEntityIds),
                        ]
                    );
                }
            }

        } while ($lastEntityId !== null);
    }
}
