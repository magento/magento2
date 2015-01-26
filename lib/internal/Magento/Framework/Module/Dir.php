<?php
/**
 * Encapsulates directories structure of a Magento module
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class Dir
{
    /**
     * Modules root directory
     *
     * @var ReadInterface
     */
    protected $_modulesDirectory;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_string;

    /**
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(Filesystem $filesystem, \Magento\Framework\Stdlib\String $string)
    {
        $this->_modulesDirectory = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->_string = $string;
    }

    /**
     * Retrieve full path to a directory of certain type within a module
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $type Type of module's directory to retrieve
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getDir($moduleName, $type = '')
    {
        $path = $this->_string->upperCaseWords($moduleName, '_', '/');
        if ($type) {
            if (!in_array($type, ['etc', 'sql', 'data', 'i18n', 'view', 'Controller'])) {
                throw new \InvalidArgumentException("Directory type '{$type}' is not recognized.");
            }
            $path .= '/' . $type;
        }

        $result = $this->_modulesDirectory->getAbsolutePath($path);

        return $result;
    }
}
