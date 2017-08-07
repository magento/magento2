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
 * @since 2.1.0
 */
class GroupRenderer implements RendererInterface
{
    /**
     * @var Quote
     * @since 2.1.0
     */
    protected $quote;

    /**
     * @param Quote $quote
     * @since 2.1.0
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
     * @since 2.1.0
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
