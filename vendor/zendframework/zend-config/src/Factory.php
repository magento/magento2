<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config;

use Zend\Stdlib\ArrayUtils;

class Factory
{
    /**
     * Plugin manager for loading readers
     *
     * @var null|ReaderPluginManager
     */
    public static $readers = null;

    /**
     * Plugin manager for loading writers
     *
     * @var null|WriterPluginManager
     */
    public static $writers = null;

    /**
     * Registered config file extensions.
     * key is extension, value is reader instance or plugin name
     *
     * @var array
     */
    protected static $extensions = array(
        'ini'         => 'ini',
        'json'        => 'json',
        'xml'         => 'xml',
        'yaml'        => 'yaml',
        'properties'  => 'javaproperties',
    );

    /**
     * Register config file extensions for writing
     * key is extension, value is writer instance or plugin name
     *
     * @var array
     */
    protected static $writerExtensions = array(
        'php'  => 'php',
        'ini'  => 'ini',
        'json' => 'json',
        'xml'  => 'xml',
        'yaml' => 'yaml',
    );

    /**
     * Read a config from a file.
     *
     * @param  string  $filename
     * @param  bool $returnConfigObject
     * @param  bool $useIncludePath
     * @return array|Config
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function fromFile($filename, $returnConfigObject = false, $useIncludePath = false)
    {
        $filepath = $filename;
        if (!file_exists($filename)) {
            if (!$useIncludePath) {
                throw new Exception\RuntimeException(sprintf(
                    'Filename "%s" cannot be found relative to the working directory',
                    $filename
                ));
            }

            $fromIncludePath = stream_resolve_include_path($filename);
            if (!$fromIncludePath) {
                throw new Exception\RuntimeException(sprintf(
                    'Filename "%s" cannot be found relative to the working directory or the include_path ("%s")',
                    $filename,
                    get_include_path()
                ));
            }
            $filepath = $fromIncludePath;
        }

        $pathinfo = pathinfo($filepath);

        if (!isset($pathinfo['extension'])) {
            throw new Exception\RuntimeException(sprintf(
                'Filename "%s" is missing an extension and cannot be auto-detected',
                $filename
            ));
        }

        $extension = strtolower($pathinfo['extension']);

        if ($extension === 'php') {
            if (!is_file($filepath) || !is_readable($filepath)) {
                throw new Exception\RuntimeException(sprintf(
                    "File '%s' doesn't exist or not readable",
                    $filename
                ));
            }

            $config = include $filepath;
        } elseif (isset(static::$extensions[$extension])) {
            $reader = static::$extensions[$extension];
            if (!$reader instanceof Reader\ReaderInterface) {
                $reader = static::getReaderPluginManager()->get($reader);
                static::$extensions[$extension] = $reader;
            }

            /** @var Reader\ReaderInterface $reader  */
            $config = $reader->fromFile($filepath);
        } else {
            throw new Exception\RuntimeException(sprintf(
                'Unsupported config file extension: .%s',
                $pathinfo['extension']
            ));
        }

        return ($returnConfigObject) ? new Config($config) : $config;
    }

    /**
     * Read configuration from multiple files and merge them.
     *
     * @param  array   $files
     * @param  bool $returnConfigObject
     * @param  bool $useIncludePath
     * @return array|Config
     */
    public static function fromFiles(array $files, $returnConfigObject = false, $useIncludePath = false)
    {
        $config = array();

        foreach ($files as $file) {
            $config = ArrayUtils::merge($config, static::fromFile($file, false, $useIncludePath));
        }

        return ($returnConfigObject) ? new Config($config) : $config;
    }

    /**
     * Writes a config to a file
     *
     * @param string $filename
     * @param array|Config $config
     * @return bool TRUE on success | FALSE on failure
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public static function toFile($filename, $config)
    {
        if (
            (is_object($config) && !($config instanceof Config)) ||
            (!is_object($config) && !is_array($config))
        ) {
            throw new Exception\InvalidArgumentException(
                __METHOD__." \$config should be an array or instance of Zend\\Config\\Config"
            );
        }

        $extension = substr(strrchr($filename, '.'), 1);
        $directory = dirname($filename);

        if (!is_dir($directory)) {
            throw new Exception\RuntimeException(
                "Directory '{$directory}' does not exists!"
            );
        }

        if (!is_writable($directory)) {
            throw new Exception\RuntimeException(
                "Cannot write in directory '{$directory}'"
            );
        }

        if (!isset(static::$writerExtensions[$extension])) {
            throw new Exception\RuntimeException(
                "Unsupported config file extension: '.{$extension}' for writing."
            );
        }

        $writer = static::$writerExtensions[$extension];
        if (($writer instanceof Writer\AbstractWriter) === false) {
            $writer = self::getWriterPluginManager()->get($writer);
            static::$writerExtensions[$extension] = $writer;
        }

        if (is_object($config)) {
            $config = $config->toArray();
        }

        $content = $writer->processConfig($config);

        return (bool) (file_put_contents($filename, $content) !== false);
    }

    /**
     * Set reader plugin manager
     *
     * @param ReaderPluginManager $readers
     * @return void
     */
    public static function setReaderPluginManager(ReaderPluginManager $readers)
    {
        static::$readers = $readers;
    }

    /**
     * Get the reader plugin manager
     *
     * @return ReaderPluginManager
     */
    public static function getReaderPluginManager()
    {
        if (static::$readers === null) {
            static::$readers = new ReaderPluginManager();
        }
        return static::$readers;
    }

    /**
     * Set writer plugin manager
     *
     * @param WriterPluginManager $writers
     * @return void
     */
    public static function setWriterPluginManager(WriterPluginManager $writers)
    {
        static::$writers = $writers;
    }

    /**
     * Get the writer plugin manager
     *
     * @return WriterPluginManager
     */
    public static function getWriterPluginManager()
    {
        if (static::$writers === null) {
            static::$writers = new WriterPluginManager();
        }

        return static::$writers;
    }

    /**
     * Set config reader for file extension
     *
     * @param  string $extension
     * @param  string|Reader\ReaderInterface $reader
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function registerReader($extension, $reader)
    {
        $extension = strtolower($extension);

        if (!is_string($reader) && !$reader instanceof Reader\ReaderInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Reader should be plugin name, class name or ' .
                'instance of %s\Reader\ReaderInterface; received "%s"',
                __NAMESPACE__,
                (is_object($reader) ? get_class($reader) : gettype($reader))
            ));
        }

        static::$extensions[$extension] = $reader;
    }

    /**
     * Set config writer for file extension
     *
     * @param string $extension
     * @param string|Writer\AbstractWriter $writer
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function registerWriter($extension, $writer)
    {
        $extension = strtolower($extension);

        if (!is_string($writer) && !$writer instanceof Writer\AbstractWriter) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer should be plugin name, class name or ' .
                'instance of %s\Writer\AbstractWriter; received "%s"',
                __NAMESPACE__,
                (is_object($writer) ? get_class($writer) : gettype($writer))
            ));
        }

        static::$writerExtensions[$extension] = $writer;
    }
}
