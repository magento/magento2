<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Interface FilenameResolverInterface
 *
 * @api
 */
interface FilenameResolverInterface
{
    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     */
    public function resolve($path);
}
