<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Table;

/**
 * Interface \Magento\Framework\Indexer\Table\StrategyInterface
 *
 * @since 2.0.0
 */
interface StrategyInterface
{
    const IDX_SUFFIX = '_idx';

    const TMP_SUFFIX = '_tmp';

    /**
     * Get IDX table usage flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getUseIdxTable();

    /**
     * Set IDX table usage flag
     *
     * @param bool $value
     *
     * @return $this
     * @since 2.0.0
     */
    public function setUseIdxTable($value = false);

    /**
     * Prepare index table name
     *
     * @param string $tablePrefix
     *
     * @return string
     * @since 2.0.0
     */
    public function prepareTableName($tablePrefix);

    /**
     * Returns target table name
     *
     * @param string $tablePrefix
     *
     * @return string
     * @since 2.0.0
     */
    public function getTableName($tablePrefix);
}
