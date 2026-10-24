<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

/**
 * An abstraction for getting context path of an asset
 *
 * @api
 */
interface ContextInterface
{
    /**
     * Get context path of an asset
     *
     * @return string
     */
    public function getPath();

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl();
}
