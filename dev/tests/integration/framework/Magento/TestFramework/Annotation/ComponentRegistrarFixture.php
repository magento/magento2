<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Implementation of the @magentoComponentsDir DocBlock annotation
 */
class ComponentRegistrarFixture
{
    /**
     * Annotation name
     */
    const ANNOTATION_NAME = 'magentoComponentsDir';

    /**#@+
     * Properties of components registrar
     */
    const REGISTRAR_CLASS = \Magento\Framework\Component\ComponentRegistrar::class;
    const PATHS_FIELD = 'paths';
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
     * @var \Magento\TestFramework\Application
     */
    private $application;

    /**
     * @var ComponentRegistrar
     */
    private $registrar;

    /**
     * Constructor
     *
     * @param string $fixtureBaseDir
     * @param \Magento\TestFramework\Application $application
     */
    public function __construct(
        $fixtureBaseDir,
        \Magento\TestFramework\Application $application,
        \Magento\Framework\Component\ComponentRegistrar $registrar
    ) {
        $this->fixtureBaseDir = $fixtureBaseDir;
        $this->application = $application;
        $this->registrar = $registrar;
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
        $annotations = $test->getAnnotations();
        $componentAnnotations = [];
        if (isset($annotations['class'][self::ANNOTATION_NAME])) {
            $componentAnnotations = array_merge($componentAnnotations, $annotations['class'][self::ANNOTATION_NAME]);
        }
        if (isset($annotations['method'][self::ANNOTATION_NAME])) {
            $componentAnnotations = array_merge($componentAnnotations, $annotations['method'][self::ANNOTATION_NAME]);
        }
        if (empty($componentAnnotations)) {
            return;
        }
        $componentAnnotations = array_unique($componentAnnotations);
        $reflection = new \ReflectionClass(self::REGISTRAR_CLASS);
        $paths = $reflection->getProperty(self::PATHS_FIELD);
        $paths->setAccessible(true);
        $this->origComponents = $paths->getValue();
        $paths->setAccessible(false);
        foreach ($componentAnnotations as $fixturePath) {
            if (strpos($fixturePath, '::') !== false) {
                list($module, $path) = explode('::', $fixturePath);
                $fixturesDir = $this->registrar->getPath(ComponentRegistrar::MODULE, $module)
                    . "/Test/Integration/" . $path;
            } else {
                $fixturesDir = $this->fixtureBaseDir . '/' . $fixturePath;
            }

            if (!file_exists($fixturesDir)) {
                throw new \InvalidArgumentException(
                    self::ANNOTATION_NAME . " fixture '$fixturesDir' does not exist"
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
        $this->application->reinitialize();
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
            $this->application->reinitialize();
        }
    }
}
