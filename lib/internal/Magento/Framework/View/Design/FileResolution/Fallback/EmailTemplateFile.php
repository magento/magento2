<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Model that finds file paths by their fileId
 * @since 2.0.0
 */
class EmailTemplateFile
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     * @since 2.0.0
     */
    protected $resolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple $resolver
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple $resolver
    ) {
        $this->resolver = $resolver;
    }

    /**
     * Get file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return bool|string
     * @since 2.0.0
     */
    public function getFile($area, ThemeInterface $themeModel, $locale, $file, $module = null)
    {
        return $this->resolver->resolve($this->getFallbackType(), $file, $area, $themeModel, $locale, $module);
    }

    /**
     * Get fallback type
     *
     * @return string
     * @since 2.0.0
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_EMAIL_TEMPLATE;
    }
}
