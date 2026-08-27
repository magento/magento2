<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

/**
 * Handling Exceptions on Design Loading
 */
class LoadDesignPlugin extends AbstractDesignLoaderPlugin
{
    /**
     * Load the design of the current area
     *
     * @return void
     */
    protected function loadPart(): void
    {
        $this->designLoader->loadDesign();
    }
}
