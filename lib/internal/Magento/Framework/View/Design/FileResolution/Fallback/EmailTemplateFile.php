<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Model that finds file paths by their fileId
 */
class EmailTemplateFile
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     */
    protected $resolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple $resolver
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
     */
    public function getFile($area, ThemeInterface $themeModel, $locale, $file, $module = null)
    {
        return $this->resolver->resolve($this->getFallbackType(), $file, $area, $themeModel, $locale, $module);
    }

    /**
     * Get fallback type
     *
     * @return string
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_EMAIL_TEMPLATE;
    }
}
