<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

/**
 * Applies store design change and user-agent design exception before the Action is executed
 *
 * Can be disabled for a specific Action that renders no themed output.
 */
class ApplyDesignChangePlugin extends AbstractDesignLoaderPlugin
{
    /**
     * Apply store design change or user-agent design exception
     *
     * @return void
     */
    protected function loadPart(): void
    {
        $this->designLoader->applyDesignChange();
    }
}
