<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\DesignInterface;

/**
 * Class RepositoryMap
 * @since 2.2.0
 */
class RepositoryMap
{
    /**
     * Name of package map file
     *
     * Map file contains list of files which are missed in current package,
     * so inherited from one of the ancestor packages
     *
     * @var string
     */
    const MAP_NAME = 'map.json';

    /**
     * Name of package result map file
     *
     * Result map contains list of all package files (own and inherited from ancestors)
     *
     * @var string
     */
    const RESULT_MAP_NAME = 'result_map.json';

    /**
     * Name of package result map file
     *
     * Result map contains list of all package files (own and inherited from ancestors)
     *
     * @var string
     */
    const REQUIRE_JS_MAP_NAME = 'requirejs-map.js';

    /**
     * @var DesignInterface
     * @since 2.2.0
     */
    private $design;

    /**
     * @var Filesystem
     * @since 2.2.0
     */
    private $filesystem;

    /**
     * @var array
     * @since 2.2.0
     */
    private $maps = [];

    /**
     * RepositoryMap constructor.
     * @param DesignInterface $design
     * @param Filesystem $filesystem
     * @since 2.2.0
     */
    public function __construct(DesignInterface $design, Filesystem $filesystem)
    {
        $this->design = $design;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $fileId
     * @param array $params
     * @return array
     * @since 2.2.0
     */
    public function getMap($fileId, array $params)
    {
        $area = $params['area'];
        $locale =  $params['locale'];
        $themePath = isset($params['theme']) ? $params['theme'] : $this->design->getThemePath($params['themeModel']);

        $path = "$area/$themePath/$locale/" . self::MAP_NAME;
        if (!isset($this->maps[$path])) {
            $this->maps[$path] = [];
            /** @var Filesystem $filesystem */
            $staticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            if ($staticDir->isFile($path)) {
                $map = $staticDir->readFile($path);
                if ($map) {
                    $this->maps[$path] = json_decode($map, true);
                }
            }
        }

        if (isset($this->maps[$path][$fileId])) {
            return $this->maps[$path][$fileId];
        }
        return [];
    }
}
