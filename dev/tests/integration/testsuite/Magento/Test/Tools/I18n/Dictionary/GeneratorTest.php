<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Tools\I18n\Dictionary;

use Magento\Tools\I18n\ServiceLocator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Tools\I18n\Dictionary\Generator
     */
    protected $generator;

    protected function setUp()
    {
        $this->testDir = realpath(__DIR__ . '/_files');
        $this->expectedDir = $this->testDir . '/expected';
        $this->source = $this->testDir . '/source';
        $this->outputFileName = $this->testDir . '/translate.csv';
        $this->generator = ServiceLocator::getDictionaryGenerator();
    }

    protected function tearDown()
    {
        if (file_exists($this->outputFileName)) {
            unlink($this->outputFileName);
        }
    }

    public function testGenerationWithoutContext()
    {
        $this->generator->generate($this->source, $this->outputFileName);

        $this->assertFileEquals($this->expectedDir . '/without_context.csv', $this->outputFileName);
    }

    public function testGenerationWithContext()
    {
        $this->generator->generate($this->source, $this->outputFileName, true);

        $this->assertFileEquals($this->expectedDir . '/with_context.csv', $this->outputFileName);
    }
}
