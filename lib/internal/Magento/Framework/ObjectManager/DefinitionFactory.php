<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Interception\Code\Generator as InterceptionGenerator;
use Magento\Framework\ObjectManager\Definition\Runtime;
use Magento\Framework\ObjectManager\Profiler\Code\Generator as ProfilerGenerator;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\ObjectManager\Definition\Compiled;
use Magento\Framework\Code\Generator\Autoloader;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefinitionFactory
{
    /**
     * Class generation dir
     *
     * @var string
     */
    protected $_generationDir;

    /**
     * Filesystem Driver
     *
     * @var DriverInterface
     */
    protected $_filesystemDriver;

    /**
     * @var \Magento\Framework\Code\Generator
     */
    protected $codeGenerator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param DriverInterface $filesystemDriver
     * @param SerializerInterface $serializer
     * @param string $generationDir
     */
    public function __construct(
        DriverInterface $filesystemDriver,
        SerializerInterface $serializer,
        $generationDir
    ) {
        $this->_filesystemDriver = $filesystemDriver;
        $this->serializer = $serializer;
        $this->_generationDir = $generationDir;
    }

    /**
     * Create class definitions
     *
     * @param mixed $definitions
     * @return DefinitionInterface
     */
    public function createClassDefinition($definitions = false)
    {
        if ($definitions) {
            if (is_string($definitions)) {
                $definitions = $this->_unpack($definitions);
            }
            $result = new Compiled($definitions);
        } else {
            $autoloader = new Autoloader($this->getCodeGenerator());
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
        return new \Magento\Framework\Interception\Definition\Runtime();
    }

    /**
     * Create relations
     *
     * @return RelationsInterface
     */
    public function createRelations()
    {
        return new \Magento\Framework\ObjectManager\Relations\Runtime();
    }

    /**
     * Un-compress definitions
     *
     * @param string $definitions
     * @return mixed
     */
    protected function _unpack($definitions)
    {
        return $this->serializer->unserialize($definitions);
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
