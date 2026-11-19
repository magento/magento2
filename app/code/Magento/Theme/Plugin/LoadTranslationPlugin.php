<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

/**
 * Loads translations of the current area before the Action is executed
 *
 * Can be disabled for a specific Action that renders no translated output.
 */
class LoadTranslationPlugin extends AbstractDesignLoaderPlugin
{
    /**
     * Load translations of the current area
     *
     * @return void
     */
    protected function loadPart(): void
    {
        $this->designLoader->loadTranslation();
    }
}
