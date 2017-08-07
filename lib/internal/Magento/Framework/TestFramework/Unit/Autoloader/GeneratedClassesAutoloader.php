<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\Code\Generator\Io;

/**
 * Autoloader that initiates auto-generation of requested classes
 * @since 2.2.0
 */
class GeneratedClassesAutoloader
{
    /**
     * @var Io
     * @since 2.2.0
     */
    private $generatorIo;

    /**
     * @var GeneratorInterface[]
     * @since 2.2.0
     */
    private $generators;

    /**
     * @param GeneratorInterface[] $generators
     * @param Io $generatorIo
     * @since 2.2.0
     */
    public function __construct(array $generators, Io $generatorIo)
    {
        foreach ($generators as $generator) {
            if (!($generator instanceof GeneratorInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "Instance of '%s' is expected, instance of '%s' is received",
                        \Magento\Framework\TestFramework\Unit\Autoloader\GeneratorInterface::class,
                        get_class($generator)
                    )
                );
            }
        }
        $this->generators = $generators;
        $this->generatorIo = $generatorIo;
    }

    /**
     * Load class
     *
     * @param string $className
     * @return bool
     * @since 2.2.0
     */
    public function load($className)
    {
        $classSourceFile = $this->generatorIo->generateResultFileName($className);
        if ($this->generatorIo->fileExists($classSourceFile)) {
            include $classSourceFile;
            return true;
        } else {
            foreach ($this->generators as $generator) {
                $content = $generator->generate($className);
                if ($content) {
                    $this->generatorIo->makeResultFileDirectory($className);
                    $this->generatorIo->writeResultFile($classSourceFile, $content);
                    include $classSourceFile;
                    return true;
                }
            };
        }

        return false;
    }
}
