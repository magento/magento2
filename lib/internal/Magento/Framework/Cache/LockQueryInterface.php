<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

interface LockQueryInterface
{
    /**
     * Make lock on data load.
     *
     * @param string $lockName
     * @param callable $dataLoader
     * @param callable $dataCollector
     * @param callable $dataSaver
     * @param callable $dataCleaner
     * @param bool $flush
     * @return array
     */
    public function lockedLoadData(
        string $lockName,
        callable $dataLoader,
        callable $dataCollector,
        callable $dataSaver,
        callable $dataCleaner,
        bool $flush = false
    );
}
