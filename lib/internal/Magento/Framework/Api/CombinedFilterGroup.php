<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

/**
 * Groups two or more filters together using 'OR' or 'AND' strategy
 */
class CombinedFilterGroup extends AbstractSimpleObject
{
    /**
     * Constants defined for keys of  data array
     */
    const FILTERS = 'filters';
    const COMBINATION_MODE = 'combination_mode';

    /**
     * Possible aggregation strategies for filters
     */
    const COMBINED_WITH_AND = 'AND';
    const COMBINED_WITH_OR = 'OR';

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
    public function setFilters(array $filters = null): self
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
     * @param string $mode
     * @return $this
     * @throws InputException
     */
    public function setCombinationMode(string $mode): self
    {
        if ($mode !== self::COMBINED_WITH_AND && $mode !== self::COMBINED_WITH_OR) {
            throw new InputException(
                new Phrase('Invalid combination mode: %1', [$mode])
            );
        }

        return $this->setData(self::COMBINATION_MODE, $mode);
    }
}
