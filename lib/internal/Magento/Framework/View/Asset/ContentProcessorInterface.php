<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\File;

/**
 * Interface ContentProcessorInterface
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function processContent(File $asset);
}
