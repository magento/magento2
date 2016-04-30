<?php
/**
 * Object manager definition factory
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\ObjectManager;

use Magento\Framework\Api\Code\Generator\Mapper as MapperGenerator;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;
use Magento\Framework\ObjectManager\Code\Generator;
use Magento\Framework\ObjectManager\Code\Generator\Converter as ConverterGenerator;
use Magento\Framework\ObjectManager\Definition\Compiled\Binary;
use Magento\Framework\ObjectManager\Definition\Compiled\Serialized;
use Magento\Framework\ObjectManager\Definition\Runtime;
use Magento\Framework\ObjectManager\Profiler\Code\Generator as ProfilerGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefinitionFactory
{
    /**
     * Directory containing compiled class metadata
     *
     * @var string
     */
    protected $_definitionDir;

    /**
     * Class generation dir
     *
     * @var string
     */
    protected $_generationDir;

    /**
     * Format of definitions
     *
     * @var string
     */
    protected $_definitionFormat;

    /**
     * Filesystem Driver
     *
     * @var DriverInterface
     */
    protected $_filesystemDriver;

    /**
     * List of definition models
     *
     * @var array
     */
    protected static $definitionClasses = [
        Binary::MODE_NAME => '\Magento\Framework\ObjectManager\Definition\Compiled\Binary',
        Serialized::MODE_NAME => '\Magento\Framework\ObjectManager\Definition\Compiled\Serialized',
    ];

    /**
     * @var \Magento\Framework\Code\Generator
     */
    protected $codeGenerator;

    /**
     * @param DriverInterface $filesystemDriver
     * @param string $definitionDir
     * @param string $generationDir
     * @param string  $definitionFormat
     */
    public function __construct(DriverInterface $filesystemDriver, $definitionDir, $generationDir, $definitionFormat)
    {
        $this->_filesystemDriver = $filesystemDriver;
        $this->_definitionDir = $definitionDir;
        $this->_generationDir = $generationDir;
        $this->_definitionFormat = $definitionFormat;
    }

    /**
     * Create class definitions
     *
     * @param mixed $definitions
     * @return Runtime
     */
    public function createClassDefinition($definitions = false)
    {
        if ($definitions) {
            if (is_string($definitions)) {
                $definitions = $this->_unpack($definitions);
            }
            $definitionModel = self::$definitionClasses[$this->_definitionFormat];
            $result = new $definitionModel($definitions);
        } else {
            $autoloader = new \Magento\Framework\Code\Generator\Autoloader($this->getCodeGenerator());
            spl_autoload_register([$autoloader, 'load']);

            $result = new Runtime();
        }
        return $result;
    }

    /**
     * Create plugin definitions
     *
     * @return \Magento\Framework\Interception\DefinitionInterface
     */
    public function createPluginDefinition()
    {
        $path = $this->_definitionDir . '/plugins.ser';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\Framework\Interception\Definition\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\Framework\Interception\Definition\Runtime();
        }
    }

    /**
     * Create relations
     *
     * @return RelationsInterface
     */
    public function createRelations()
    {
        $path = $this->_definitionDir . '/' . 'relations.ser';
        if ($this->_filesystemDriver->isReadable($path)) {
            return new \Magento\Framework\ObjectManager\Relations\Compiled(
                $this->_unpack($this->_filesystemDriver->fileGetContents($path))
            );
        } else {
            return new \Magento\Framework\ObjectManager\Relations\Runtime();
        }
    }

    /**
     * Gets supported definition formats
     *
     * @return array
     */
    public static function getSupportedFormats()
    {
        return array_keys(self::$definitionClasses);
    }

    /**
     * Un-compress definitions
     *
     * @param string $definitions
     * @return mixed
     */
    protected function _unpack($definitions)
    {
        $extractor = $this->_definitionFormat == Binary::MODE_NAME ? 'igbinary_unserialize' : 'unserialize';
        return $extractor($definitions);
    }

    /**
     * Get existing code generator. Instantiate a new one if it does not exist yet.
     *
     * @return \Magento\Framework\Code\Generator
     */
    public function getCodeGenerator()
    {
        if (!$this->codeGenerator) {
            $generatorIo = new \Magento\Framework\Code\Generator\Io(
                $this->_filesystemDriver,
                $this->_generationDir
            );
            $this->codeGenerator = new \Magento\Framework\Code\Generator($generatorIo);
        }
        return $this->codeGenerator;
    }
}
