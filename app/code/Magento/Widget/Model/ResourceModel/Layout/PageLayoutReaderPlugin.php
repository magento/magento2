<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Model\ResourceModel\Layout;

use Magento\Framework\View\Layout\Reader\Context as ReaderContext;
use Magento\Framework\View\Page\Layout\Reader as PageLayoutReader;

/**
 * Tells whether layout updates are being merged for the page layout reader
 */
class PageLayoutReaderPlugin
{
    /**
     * @var int
     */
    private $readDepth = 0;

    /**
     * Mark the page layout merge as running for the duration of the read
     *
     * @param PageLayoutReader $subject
     * @param callable $proceed
     * @param ReaderContext $readerContext
     * @param string $pageLayout
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRead(
        PageLayoutReader $subject,
        callable $proceed,
        ReaderContext $readerContext,
        $pageLayout
    ) {
        $this->readDepth++;
        try {
            $proceed($readerContext, $pageLayout);
        } finally {
            $this->readDepth--;
        }
    }

    /**
     * Whether the page layout reader is merging the page layout
     *
     * @return bool
     */
    public function isReading(): bool
    {
        return $this->readDepth > 0;
    }
}
