<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Implementation of the @magentoComponentsDir DocBlock annotation
 */
class ComponentRegistrarFixture
{
    public const ANNOTATION_NAME = 'magentoComponentsDir';

    /**#@+
     * Properties of components registrar
     */
    public const REGISTRAR_CLASS = \Magento\Framework\Component\ComponentRegistrar::class;
    public const PATHS_FIELD = 'paths';
    /**#@-*/

    /**
     * Fixtures base dir
     *
     * @var string
     */
    private $fixtureBaseDir;

    /**
     * Original values of registered components
     *
     * @var array
     */
    private $origComponents = null;

    /**
     * Constructor
     *
     * @param string $fixtureBaseDir
     */
    public function __construct($fixtureBaseDir)
    {
        $this->fixtureBaseDir = $fixtureBaseDir;
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->registerComponents($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->restoreComponents();
    }

    /**
     * Register fixture components
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    private function registerComponents(\PHPUnit\Framework\TestCase $test)
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\ComponentsDir::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\ComponentsDir::class)
                    ]
                ]
            );
        $values = $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);
        $componentAnnotations = array_unique(array_column($values, 'path'));
        $reflection = new \ReflectionClass(self::REGISTRAR_CLASS);
        $paths = $reflection->getProperty(self::PATHS_FIELD);
        $paths->setAccessible(true);
        $this->origComponents = $paths->getValue();
        $paths->setAccessible(false);
        foreach ($componentAnnotations as $fixturePath) {
            $fixturesDir = $this->fixtureBaseDir . '/' . $fixturePath;
            if (!file_exists($fixturesDir)) {
                throw new \InvalidArgumentException(
                    self::ANNOTATION_NAME . " fixture '$fixturePath' does not exist"
                );
            }
            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($fixturesDir, \FilesystemIterator::SKIP_DOTS)
                ),
                '/^.+\/registration\.php$/'
            );
            /**
             * @var \SplFileInfo $registrationFile
             */
            foreach ($iterator as $registrationFile) {
                require $registrationFile->getRealPath();
            }
        }
    }

    /**
     * Restore registered components list to the original
     */
    private function restoreComponents()
    {
        if (null !== $this->origComponents) {
            $reflection = new \ReflectionClass(self::REGISTRAR_CLASS);
            $paths = $reflection->getProperty(self::PATHS_FIELD);
            $paths->setAccessible(true);
            $paths->setValue($this->origComponents);
            $paths->setAccessible(false);
            $this->origComponents = null;
        }
    }
}
