<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use FilesystemIterator;
use InvalidArgumentException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Annotation\Parser\Composite;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use SplFileInfo;
use Throwable;

/**
 * Implementation of the @magentoComponentsDir DocBlock annotation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ComponentRegistrarFixture
{
    public const ANNOTATION_NAME = 'magentoComponentsDir';

    /**#@+
     * Properties of components registrar
     */
    public const REGISTRAR_CLASS = ComponentRegistrar::class;
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
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
    {
        $this->registerComponents($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(TestCase $test)
    {
        $this->restoreComponents();
    }

    /**
     * Register fixture components
     *
     * @param TestCase $test
     */
    private function registerComponents(TestCase $test)
    {
        $values = [];
        try {
            $values = $this->parse($test);
        } catch (Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse fixtures',
                get_class($test),
                $test->getName(false),
                $exception
            );
        }
        if (!$values) {
            return;
        }

        $componentAnnotations = array_unique(array_column($values, 'path'));
        $reflection = new ReflectionClass(self::REGISTRAR_CLASS);
        $paths = $reflection->getProperty(self::PATHS_FIELD);
        $paths->setAccessible(true);
        $this->origComponents = $paths->getValue();
        $paths->setAccessible(false);
        foreach ($componentAnnotations as $fixturePath) {
            $fixturesDir = $this->fixtureBaseDir . '/' . $fixturePath;
            if (!file_exists($fixturesDir)) {
                throw new InvalidArgumentException(
                    self::ANNOTATION_NAME . " fixture '$fixturePath' does not exist"
                );
            }
            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($fixturesDir, FilesystemIterator::SKIP_DOTS)
                ),
                '/^.+\/registration\.php$/'
            );
            /**
             * @var SplFileInfo $registrationFile
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
            $reflection = new ReflectionClass(self::REGISTRAR_CLASS);
            $paths = $reflection->getProperty(self::PATHS_FIELD);
            $paths->setAccessible(true);
            $paths->setValue($this->origComponents);
            $paths->setAccessible(false);
            $this->origComponents = null;
        }
    }

    /**
     * Returns ComponentsDir fixtures configuration
     *
     * @param TestCase $test
     * @return array
     * @throws LocalizedException
     */
    private function parse(TestCase $test): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\ComponentsDir::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\ComponentsDir::class)
                    ]
                ]
            );
        return array_merge(
            $parsers->parse($test, ParserInterface::SCOPE_CLASS),
            $parsers->parse($test, ParserInterface::SCOPE_METHOD)
        );
    }
}
