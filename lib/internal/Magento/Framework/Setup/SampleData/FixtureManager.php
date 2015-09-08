<?php

namespace Magento\Framework\Setup\SampleData;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class FixtureManager
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
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(Filesystem $filesystem, \Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->_modulesDirectory = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->_string = $string;
    }

    public function getFixture($fileId)
    {
        list($moduleName, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->normalizePath($fileId)
        );

        $path = $this->_string->upperCaseWords($moduleName, '_', '/') . '/' . $filePath;
        $result = $this->_modulesDirectory->getAbsolutePath($path);

        return $result;

    }

    /**
     * Remove excessive "." and ".." parts from a path
     *
     * For example foo/bar/../file.ext -> foo/file.ext
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $parts = explode('/', $path);
        $result = [];

        foreach ($parts as $part) {
            if ('..' === $part) {
                if (!count($result) || ($result[count($result) - 1] == '..')) {
                    $result[] = $part;
                } else {
                    array_pop($result);
                }
            } elseif ('.' !== $part) {
                $result[] = $part;
            }
        }
        return implode('/', $result);
    }
}
