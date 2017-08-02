<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\FileResolution\Fallback;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Interface for resolvers of view files using fallback rules
 * @since 2.0.0
 */
interface ResolverInterface
{
    /**
     * Get path of file after using fallback rules
     *
     * @param string $type
     * @param string $file
     * @param string|null $area
     * @param ThemeInterface|null $theme
     * @param string|null $locale
     * @param string|null $module
     * @return string|bool
     * @since 2.0.0
     */
    public function resolve($type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null);
}
