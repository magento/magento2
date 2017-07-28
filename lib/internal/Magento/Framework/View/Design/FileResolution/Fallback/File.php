<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Provider of dynamic view files
 * @since 2.0.0
 */
class File
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
     * Get existing file name, using fallback mechanism
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string|bool
     * @since 2.0.0
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        return $this->resolver->resolve($this->getFallbackType(), $file, $area, $themeModel, null, $module);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE;
    }
}
