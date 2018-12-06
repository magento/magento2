<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Template\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

/**
 * Class Validator.
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
     */
    protected $_templatesValidationResults = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\FileSystem
     */
    protected $_filesystem;

    /**
     * Allow symlinks flag
     *
     * @var bool
     */
    protected $_isAllowSymlinks = false;

    /**
     * Root directory
     *
     * @var bool
     */
    protected $directory = null;

    /**
     * Themes directory
     *
     * @var string
     */
    protected $_themesDir;

    /**
     * Application directory
     *
     * @var string
     */
    protected $_appDir;

    /**
     * Compiled templates directory
     *
     * @var string
     */
    protected $_compiledDir;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param ComponentRegistrar $componentRegistrar
     * @param string|null $scope
     * @param FileDriver|null $fileDriver
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        ComponentRegistrar $componentRegistrar,
        $scope = null,
        FileDriver $fileDriver = null
    ) {
        $this->_filesystem = $filesystem;
        $this->_isAllowSymlinks = $scopeConfigInterface->getValue(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK, $scope);
        $this->_themesDir = $componentRegistrar->getPaths(ComponentRegistrar::THEME);
        $this->moduleDirs = $componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        $this->_compiledDir = $this->_filesystem->getDirectoryRead(DirectoryList::TEMPLATE_MINIFICATION_DIR)
            ->getAbsolutePath();
        $this->fileDriver = $fileDriver ?: \Magento\Framework\App\ObjectManager::getInstance()->get(FileDriver::class);
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
     */
    protected function isPathInDirectories($path, $directories)
    {
        if (!is_array($directories)) {
            $directories = (array)$directories;
        }
        foreach ($directories as $directory) {
            if (0 === strpos($this->fileDriver->getRealPath($path), $directory)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Instantiates filesystem directory
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected function getRootDirectory()
    {
        if (null === $this->directory) {
            $this->directory = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        }
        return $this->directory;
    }
}
