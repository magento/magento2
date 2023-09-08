<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Plugin;

use Magento\Framework\Indexer\DeferredCacheCleanerInterface;
use Magento\ImportExport\Model\Import;

class DeferCacheCleaningUntilImportIsComplete
{
    /**
     * @var DeferredCacheCleanerInterface
     */
    private $cacheCleaner;

    /**
     * @param DeferredCacheCleanerInterface $cacheCleaner
     */
    public function __construct(DeferredCacheCleanerInterface $cacheCleaner)
    {
        $this->cacheCleaner = $cacheCleaner;
    }

    /**
     * Start deferred cache before stock items save
     *
     * @param Import $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeImportSource(Import $subject): void
    {
        $this->cacheCleaner->start();
    }

    /**
     * Flush deferred cache after stock items save
     *
     * @param Import $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportSource(Import $subject, bool $result): bool
    {
        $this->cacheCleaner->flush();
        return $result;
    }
}
