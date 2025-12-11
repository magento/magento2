<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\File;

/**
 * Interface ContentProcessorInterface
 *
 * @api
 */
interface ContentProcessorInterface
{
    /**
     * Error prefix
     */
    const ERROR_MESSAGE_PREFIX = 'Compilation from source: ';

    /**
     * Process file content
     *
     * @param File $asset
     * @return string
     */
    public function processContent(File $asset);
}
