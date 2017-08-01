<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class \Magento\Framework\Setup\SampleData\FixtureManager
 *
 * @since 2.0.0
 */
class FixtureManager
{
    /**
     * @var ComponentRegistrar
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * Modules root directory
     *
     * @var ReadInterface
     * @since 2.0.0
     */
    protected $_modulesDirectory;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $_string;

    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @since 2.0.0
     */
    public function __construct(ComponentRegistrar $componentRegistrar, \Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->_string = $string;
    }

    /**
     * @param string $fileId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getFixture($fileId)
    {
        list($moduleName, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->normalizePath($fileId)
        );
        return $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName) . '/' . $filePath;
    }

    /**
     * Remove excessive "." and ".." parts from a path
     *
     * For example foo/bar/../file.ext -> foo/file.ext
     *
     * @param string $path
     * @return string
     * @since 2.0.0
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
