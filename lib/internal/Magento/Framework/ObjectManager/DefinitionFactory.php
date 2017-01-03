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
     * @param DriverInterface $filesystemDriver
     * @param string $generationDir
     */
    public function __construct(
        DriverInterface $filesystemDriver,
        $generationDir
    ) {
        $this->_filesystemDriver = $filesystemDriver;
        $this->_generationDir = $generationDir;
    }

    /**
     * Create class definitions
     *
     * @return DefinitionInterface
     */
    public function createClassDefinition()
    {
        $autoloader = new Autoloader($this->getCodeGenerator());
        spl_autoload_register([$autoloader, 'load']);
        return new Runtime();
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
