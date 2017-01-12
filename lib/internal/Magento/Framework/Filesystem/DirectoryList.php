<?php
/**
 * Application file system directories dictionary
 *
 * Provides information about what directories are available in the application
 * Serves as customizaiton point to specify different directories or add own
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem;

/**
 * A list of directories
 *
 * Each list item consists of:
 * - a directory path in the filesystem
 * - optionally, a URL path
 *
 * This object is intended to be immutable (a "value object").
 * The defaults are pre-defined and can be modified only by inheritors of this class.
 * Through the constructor, it is possible to inject custom paths or URL paths, but impossible to inject new types.
 */
class DirectoryList
{
    /**#@+
     * Keys of directory configuration
     */
    const PATH = 'path';
    const URL_PATH = 'uri';
    /**#@- */

    /**
     * System base temporary directory
     */
    const SYS_TMP = 'sys_tmp';

    /**
     * Root path
     *
     * @var string
     */
    private $root;

    /**
     * Directories configurations
     *
     * @var array
     */
    private $directories;

    /**
     * Predefined types/paths
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [self::SYS_TMP => [self::PATH => '']];
    }

    /**
     * Validates format and contents of given configuration
     *
     * @param array $config
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function validate($config)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Unexpected value type.');
        }
        $defaultConfig = static::getDefaultConfig();
        foreach ($config as $type => $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException('Unexpected value type.');
            }
            if (!isset($defaultConfig[$type])) {
                throw new \InvalidArgumentException("Unknown type: {$type}");
            }
            if (!isset($row[self::PATH]) && !isset($row[self::URL_PATH])) {
                throw new \InvalidArgumentException("Missing required keys at: {$type}");
            }
        }
    }

    /**
     * Constructor
     *
     * @param string $root
     * @param array $config
     */
    public function __construct($root, array $config = [])
    {
        static::validate($config);
        $this->root = $this->normalizePath($root);
        $this->directories = static::getDefaultConfig();
        $this->directories[self::SYS_TMP] = [self::PATH => realpath(sys_get_temp_dir())];

        // inject custom values from constructor
        foreach ($this->directories as $code => $dir) {
            foreach ([self::PATH, self::URL_PATH] as $key) {
                if (isset($config[$code][$key])) {
                    $this->directories[$code][$key] = $config[$code][$key];
                }
            }
        }

        // filter/validate values
        foreach ($this->directories as $code => $dir) {
            $path = $this->normalizePath($dir[self::PATH]);
            if (!$this->isAbsolute($path)) {
                $path = $this->prependRoot($path);
            }
            $this->directories[$code][self::PATH] = $path;

            if (isset($dir[self::URL_PATH])) {
                $this->assertUrlPath($dir[self::URL_PATH]);
            }
        }
    }

    /**
     * Converts slashes in path to a conventional unix-style
     *
     * @param string $path
     * @return string
     */
    private function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Validates a URL path
     *
     * Path must be usable as a fragment of a URL path.
     * For interoperability and security purposes, no uppercase or "upper directory" paths like "." or ".."
     *
     * @param string $urlPath
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertUrlPath($urlPath)
    {
        if (!preg_match('/^([a-z0-9_]+[a-z0-9\._]*(\/[a-z0-9_]+[a-z0-9\._]*)*)?$/', $urlPath)) {
            throw new \InvalidArgumentException(
                "URL path must be relative directory path in lowercase with '/' directory separator: '{$urlPath}'"
            );
        }
    }

    /**
     * Concatenates root directory path with a relative path
     *
     * @param string $path
     * @return string
     */
    protected function prependRoot($path)
    {
        $root = $this->getRoot();
        return $root . ($root && $path ? '/' : '') . $path;
    }

    /**
     * Determine if a path is absolute
     *
     * @param string $path
     * @return bool
     */
    protected function isAbsolute($path)
    {
        $path = strtr($path, '\\', '/');

        if (strpos($path, '/') === 0) {
            //is UnixRoot
            return true;
        } elseif (preg_match('#^\w{1}:/#', $path)) {
            //is WindowsRoot
            return true;
        } elseif (parse_url($path, PHP_URL_SCHEME) !== null) {
            //is WindowsLetter
            return true;
        }

        return false;
    }

    /**
     * Gets a filesystem path of the root directory
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Gets a filesystem path of a directory
     *
     * @param string $code
     * @return string
     */
    public function getPath($code)
    {
        $this->assertCode($code);
        return $this->directories[$code][self::PATH];
    }

    /**
     * Gets URL path of a directory
     *
     * @param string $code
     * @return string|bool
     */
    public function getUrlPath($code)
    {
        $this->assertCode($code);
        if (!isset($this->directories[$code][self::URL_PATH])) {
            return false;
        }
        return $this->directories[$code][self::URL_PATH];
    }

    /**
     * Asserts that specified directory code is in the registry
     *
     * @param string $code
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    private function assertCode($code)
    {
        if (!isset($this->directories[$code])) {
            throw new \Magento\Framework\Exception\FileSystemException(
                new \Magento\Framework\Phrase('Unknown directory type: \'%1\'', [$code])
            );
        }
    }
}
