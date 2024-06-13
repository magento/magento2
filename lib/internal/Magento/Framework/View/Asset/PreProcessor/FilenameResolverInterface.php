<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
