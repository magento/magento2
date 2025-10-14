<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\I18n\Dictionary\Generator;
use Magento\Setup\Module\I18n\ServiceLocator;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $testDir;

    /**
     * @var string
     */
    protected $expectedDir;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $outputFileName;

    /**
     * @var \Magento\Setup\Module\I18n\Dictionary\Generator
     */
    protected $generator;

    /**
     * @var array
     */
    protected $backupRegistrar;

    protected function setUp(): void
    {
        $reflection = new \ReflectionClass(\Magento\Framework\Component\ComponentRegistrar::class);
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $this->backupRegistrar = $paths->getValue();
        $paths->setValue(null, ['module' => [], 'theme' => []]);
        $paths->setAccessible(false);

        $this->testDir = realpath(__DIR__ . '/_files');
        $this->expectedDir = $this->testDir . '/expected';
        $this->source = $this->testDir . '/source';
        $this->outputFileName = $this->testDir . '/translate.csv';

        // Register the test modules
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Magento_FirstModule',
            $this->source . '/app/code/Magento/FirstModule'
        );
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'Magento_SecondModule',
            $this->source . '/app/code/Magento/SecondModule'
        );

        // Register the test theme
        ComponentRegistrar::register(
            ComponentRegistrar::THEME,
            'adminhtml/default/backend',
            $this->source . '/app/design/adminhtml/default/backend'
        );

        $this->generator = ServiceLocator::getDictionaryGenerator();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->outputFileName)) {
            unlink($this->outputFileName);
        }
        $property = new \ReflectionProperty(\Magento\Setup\Module\I18n\ServiceLocator::class, '_dictionaryGenerator');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $property->setAccessible(false);

        $reflection = new \ReflectionClass(\Magento\Framework\Component\ComponentRegistrar::class);
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $paths->setValue(null, $this->backupRegistrar);
        $paths->setAccessible(false);
    }

    public function testGenerationWithoutContext()
    {
        $this->generator->generate($this->source, $this->outputFileName);

        $this->assertFileEquals($this->expectedDir . '/without_context.csv', $this->outputFileName);
    }

    public function testGenerationWithContext()
    {
        $this->generator->generate($this->source, $this->outputFileName, true);

        $expected = explode(PHP_EOL, file_get_contents($this->expectedDir . '/with_context.csv'));
        $output = file_get_contents($this->outputFileName);
        foreach ($expected as $line) {
            if ($line) {
                $this->assertStringContainsString($line, $output);
            }
        }
    }
}
