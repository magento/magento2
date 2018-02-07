<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Platform;
use Magento\Framework\DB\Platform\Quote;

/**
 * Class GroupRenderer
 */
class GroupRenderer implements RendererInterface
{
    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @param Quote $quote
     */
    public function __construct(
        Quote $quote
    ) {
        $this->quote = $quote;
    }

    /**
     * Render GROUP BY section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FROM) && $select->getPart(Select::GROUP)) {
            $group = [];
            foreach ($select->getPart(Select::GROUP) as $term) {
                $group[] = $this->quote->quoteIdentifier($term);
            }
            $sql .= ' ' . Select::SQL_GROUP_BY . ' ' . implode(",\n\t", $group);
        }
        return $sql;
    }
}
