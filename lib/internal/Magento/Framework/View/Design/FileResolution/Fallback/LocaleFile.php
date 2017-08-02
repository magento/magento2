<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Provider of localized view files
 * @since 2.0.0
 */
class LocaleFile
{
    /**
     * @var ResolverInterface
     * @since 2.0.0
     */
    private $resolver;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     * @since 2.0.0
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get locale file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @return string|bool
     * @since 2.0.0
     */
    public function getFile($area, ThemeInterface $themeModel, $locale, $file)
    {
        return $this->resolver->resolve($this->getFallbackType(), $file, $area, $themeModel, $locale, null);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE;
    }
}
