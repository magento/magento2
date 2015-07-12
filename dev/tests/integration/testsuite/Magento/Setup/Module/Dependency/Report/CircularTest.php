<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report;

use Magento\Setup\Module\Dependency\ServiceLocator;

class CircularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var string
     */
    protected $sourceFilename;

    /**
     * @var BuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->fixtureDir = realpath(__DIR__ . '/../_files') . '/';
        $this->sourceFilename = $this->fixtureDir . 'circular-dependencies.csv';

        $this->builder = ServiceLocator::getCircularDependenciesReportBuilder();
    }

    public function testBuild()
    {
        $this->builder->build(
            [
                'parse' => [
                    'files_for_parse' => [$this->fixtureDir . 'composer4.json', $this->fixtureDir . 'composer5.json'],
                ],
                'write' => ['report_filename' => $this->sourceFilename],
            ]
        );

        $this->assertFileEquals($this->fixtureDir . 'expected/circular-dependencies.csv', $this->sourceFilename);
    }

    public function testBuildWithoutDependencies()
    {
        $this->builder->build(
            [
                'parse' => ['files_for_parse' => [$this->fixtureDir . 'composer3.json']],
                'write' => ['report_filename' => $this->sourceFilename],
            ]
        );

        $this->assertFileEquals(
            $this->fixtureDir . 'expected/without-circular-dependencies.csv',
            $this->sourceFilename
        );
    }

    public function tearDown()
    {
        if (file_exists($this->sourceFilename)) {
            unlink($this->sourceFilename);
        }
    }
}
