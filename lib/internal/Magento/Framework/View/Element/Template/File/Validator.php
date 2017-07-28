<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Template\File;

use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Class Validator
 * @package Magento\Framework\View\Element\Template\File
 * @since 2.0.0
 */
class Validator
{
    /**
     * Config path to 'Allow Symlinks' template settings
     */
    const XML_PATH_TEMPLATE_ALLOW_SYMLINK = 'dev/template/allow_symlink';

    /**
     * Template files map
     *
     * @var []
     * @since 2.0.0
     */
    protected $_templatesValidationResults = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\FileSystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * Allow symlinks flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isAllowSymlinks = false;

    /**
     * Root directory
     *
     * @var bool
     * @since 2.0.0
     */
    protected $directory = null;

    /**
     * Themes directory
     *
     * @var string
     * @since 2.0.0
     */
    protected $_themesDir;

    /**
     * Application directory
     *
     * @var string
     * @since 2.0.0
     */
    protected $_appDir;

    /**
     * Compiled templates directory
     *
     * @var string
     * @since 2.0.0
     */
    protected $_compiledDir;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param ComponentRegistrar $componentRegistrar
     * @param string|null $scope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        ComponentRegistrar $componentRegistrar,
        $scope = null
    ) {
        $this->_filesystem = $filesystem;
        $this->_isAllowSymlinks = $scopeConfigInterface->getValue(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK, $scope);
        $this->_themesDir = $componentRegistrar->getPaths(ComponentRegistrar::THEME);
        $this->moduleDirs = $componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        $this->_compiledDir = $this->_filesystem->getDirectoryRead(DirectoryList::TMP_MATERIALIZATION_DIR)
            ->getAbsolutePath();
    }

    /**
     * Checks whether the provided file can be rendered.
     *
     * Available directories which are allowed to be rendered
     * (the template file should be located under these directories):
     *  - app
     *  - design
     *
     * @param string $filename
     * @return bool
     * @since 2.0.0
     */
    public function isValid($filename)
    {
        $filename = str_replace('\\', '/', $filename);
        if (!isset($this->_templatesValidationResults[$filename])) {
            $this->_templatesValidationResults[$filename] =
                ($this->isPathInDirectories($filename, $this->_compiledDir)
                    || $this->isPathInDirectories($filename, $this->moduleDirs)
                    || $this->isPathInDirectories($filename, $this->_themesDir)
                    || $this->_isAllowSymlinks)
                && $this->getRootDirectory()->isFile($this->getRootDirectory()->getRelativePath($filename));
        }
        return $this->_templatesValidationResults[$filename];
    }

    /**
     * Checks whether path related to the directory
     *
     * @param string $path
     * @param string|array $directories
     * @return bool
     * @since 2.0.0
     */
    protected function isPathInDirectories($path, $directories)
    {
        if (!is_array($directories)) {
            $directories = (array)$directories;
        }
        foreach ($directories as $directory) {
            if (0 === strpos($path, $directory)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Instantiates filesystem directory
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected function getRootDirectory()
    {
        if (null === $this->directory) {
            $this->directory = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        }
        return $this->directory;
    }
}
