<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\FilterGroupInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\InputException;

/**
 * Groups two or more filters together using 'OR' or 'AND' strategy
 */
class CombinedFilterGroup extends AbstractSimpleObject implements FilterGroupInterface
{
    const FILTERS = 'filters';
    const COMBINATION_MODE = 'combination_mode';

    /**
     * Possible aggregation strategies for filters
     */
    const COMBINED_WITH_AND = Select::SQL_AND;
    const COMBINED_WITH_OR = Select::SQL_OR;

    /**
     * Returns a list of filters in this group
     *
     * @return \Magento\Framework\Api\Filter[]|null
     */
    public function getFilters()
    {
        $filters = $this->_get(self::FILTERS);
        return $filters === null ? [] : $filters;
    }

    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFilters(array $filters = null)
    {
        return $this->setData(self::FILTERS, $filters);
    }

    /**
     * @return mixed|null
     */
    public function getCombinationMode()
    {
        return $this->_get(self::COMBINATION_MODE);
    }

    /**
     * @param $mode
     * @return $this
     * @throws InputException
     */
    public function setCombinationMode($mode)
    {
        if ($mode !== self::COMBINED_WITH_AND && $mode !== self::COMBINED_WITH_OR) {
            throw new InputException(
                __(sprintf('Invalid combination mode: %s', $mode))
            );
        }

        return $this->setData(self::COMBINATION_MODE, $mode);
    }
}
