<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\ViewModel;

interface ContentProviderInterface
{
    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent(): ?string;
}
