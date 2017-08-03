<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Factory that produces view file instances
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Return newly created instance of a view file
     *
     * @param string $filename
     * @param string $module
     * @param ThemeInterface|null $theme
     * @param bool $isBase
     * @return \Magento\Framework\View\File
     * @since 2.0.0
     */
    public function create($filename, $module = '', ThemeInterface $theme = null, $isBase = false)
    {
        return $this->objectManager->create(
            \Magento\Framework\View\File::class,
            ['filename' => $filename, 'module' => $module, 'theme' => $theme, 'isBase' => $isBase]
        );
    }
}
