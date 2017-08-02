<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Options;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Dictionary generator options resolver
 * @since 2.0.0
 */
class Resolver implements ResolverInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $directory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $withContext;

    /**
     * @var ComponentRegistrar
     * @since 2.0.0
     */
    protected $componentRegistrar;

    /**
     * Resolver construct
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param string $directory
     * @param bool $withContext
     * @since 2.0.0
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        $directory,
        $withContext
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->directory = $directory;
        $this->withContext = $withContext;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getOptions()
    {
        if (null === $this->options) {
            if ($this->withContext) {
                $directory = rtrim($this->directory, '\\/');
                $this->directory = ($directory == '.' || $directory == '..') ? BP : realpath($directory);
                $moduleDirs = $this->getComponentDirectories(ComponentRegistrar::MODULE);
                $themeDirs = $this->getComponentDirectories(ComponentRegistrar::THEME);

                $this->options = [
                    [
                        'type' => 'php',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.(php|phtml)$/',
                    ],
                    [
                        'type' => 'html',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.html$/',
                    ],
                    [
                        'type' => 'js',
                        'paths' => array_merge(
                            $moduleDirs,
                            $themeDirs,
                            [
                                $this->directory . '/lib/web/mage/',
                                $this->directory . '/lib/web/varien/',
                            ]
                        ),
                        'fileMask' => '/\.(js|phtml)$/'
                    ],
                    [
                        'type' => 'xml',
                        'paths' => array_merge($moduleDirs, $themeDirs),
                        'fileMask' => '/\.xml$/'
                    ],
                ];
            } else {
                $this->options = [
                    ['type' => 'php', 'paths' => [$this->directory], 'fileMask' => '/\.(php|phtml)$/'],
                    ['type' => 'html', 'paths' => [$this->directory], 'fileMask' => '/\.html/'],
                    ['type' => 'js', 'paths' => [$this->directory], 'fileMask' => '/\.(js|phtml)$/'],
                    ['type' => 'xml', 'paths' => [$this->directory], 'fileMask' => '/\.xml$/'],
                ];
            }
            foreach ($this->options as $option) {
                $this->isValidPaths($option['paths']);
            }
        }
        return $this->options;
    }

    /**
     * @param array $directories
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function isValidPaths($directories)
    {
        foreach ($directories as $path) {
            if (!is_dir($path)) {
                if ($this->withContext) {
                    throw new \InvalidArgumentException('Specified path is not a Magento root directory');
                } else {
                    throw new \InvalidArgumentException('Specified path doesn\'t exist');
                }
            }
        }
    }

    /**
     * Get the given type component directories
     *
     * @param string $componentType
     * @return array
     * @since 2.0.0
     */
    private function getComponentDirectories($componentType)
    {
        $dirs = [];
        foreach ($this->componentRegistrar->getPaths($componentType) as $componentDir) {
            $dirs[] = $componentDir . '/';
        }
        return $dirs;
    }
}
