<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;

/**
 * Encapsulates directories structure of a Magento theme
 */
class Dir
{
    /**
     * Theme registry
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Retrieve area configuration for a theme path
     *
     * @param string $path
     * @return array
     */
    public function getAreaConfiguration($path)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::THEME) as $key => $themePath) {
            if (strpos($themePath, $path) !== false) {
                $pathPieces = explode('/', $key);
                $area = array_shift($pathPieces);
                return ['area' => $area, 'theme_path_pieces' => $pathPieces];
            }
        }
    }
}
