<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for theme packages
 */
class ThemePackageFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create an instance of ThemePackage
     *
     * @param string $key
     * @param string $path
     *
     * @return ThemePackage
     */
    public function create($key, $path)
    {
        return $this->objectManager->create(
            'Magento\Framework\View\Design\Theme\ThemePackage',
            ['key' => $key, 'path' => $path]
        );
    }
}
