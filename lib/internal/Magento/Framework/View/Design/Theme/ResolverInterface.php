<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme resolver interface
 * @since 2.0.0
 */
interface ResolverInterface
{
    /**
     * Retrieve instance of a theme currently used in an area
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function get();
}
