<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Interface FilenameResolverInterface
 * @since 2.0.0
 */
interface FilenameResolverInterface
{
    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     * @since 2.0.0
     */
    public function resolve($path);
}
