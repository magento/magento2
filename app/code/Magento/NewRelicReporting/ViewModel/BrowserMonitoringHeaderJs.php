<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class BrowserMonitoringHeaderJs implements ArgumentInterface, ContentProviderInterface
{
    /**
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        private readonly NewRelicWrapper $newRelicWrapper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getContent(): ?string
    {
        return $this->newRelicWrapper->isAutoInstrumentEnabled()
            ? $this->newRelicWrapper->getBrowserTimingHeader(false)
            : null;
    }
}
