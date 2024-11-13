<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Table;

/**
 * Interface \Magento\Framework\Indexer\Table\StrategyInterface
 *
 * @api
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
     */
    public function getUseIdxTable();

    /**
     * Set IDX table usage flag
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setUseIdxTable($value = false);

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
