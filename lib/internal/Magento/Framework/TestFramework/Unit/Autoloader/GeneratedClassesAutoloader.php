<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\Code\Generator\Io;

/**
 * Autoloader that initiates auto-generation of requested classes
 */
class GeneratedClassesAutoloader
{
    /**
     * @var Io
     */
    private $generatorIo;

    /**
     * @var GeneratorInterface[]
     */
    private $generators;

    /**
     * @param GeneratorInterface[] $generators
     * @param Io $generatorIo
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
