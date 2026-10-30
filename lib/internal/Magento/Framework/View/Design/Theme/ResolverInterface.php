<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme resolver interface
 *
 * @api
 */
interface ResolverInterface
{
    /**
     * Retrieve instance of a theme currently used in an area
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function get();
}
