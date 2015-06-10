<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Indexer\Table;

/**
 * Interface StrategyInterface
 * @package Magento\Indexer
 */
interface StrategyInterface
{
    const IDX_SUFFIX = '_idx';

    const TMP_SUFFIX = '_tmp';

    /**
     * Is direct table writing required
     *
     * @param bool $value
     *
     * @return bool
     */
    public function useIdxTable($value = null);

    /**
     * Prepare index table name
     *
     * @param string $tablePrefix
     *
     * @return string
     */
    public function prepareTableName($tablePrefix);

    /**
     * Returns target table name
     *
     * @param string $tablePrefix
     *
     * @return string
     */
    public function getTableName($tablePrefix);
}
