<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report;

use Magento\Setup\Module\Dependency\ServiceLocator;

class FrameworkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var string
     */
    protected $fixtureDirModule;

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
        $this->fixtureDirModule = $this->fixtureDir . 'code/Magento/FirstModule/';
        $this->sourceFilename = $this->fixtureDir . 'framework-dependencies.csv';

        $this->builder = ServiceLocator::getFrameworkDependenciesReportBuilder();
    }

    public function testBuild()
    {
        $this->builder->build(
            [
                'parse' => [
                    'files_for_parse' => [
                        $this->fixtureDirModule . 'Helper/Helper.php',
                        $this->fixtureDirModule . 'Model/Model.php',
                        $this->fixtureDirModule . 'view/frontend/template.phtml',
                    ],
                    'config_files' => [$this->fixtureDirModule . 'etc/module.xml'],
                    'declared_namespaces' => ['Magento'],
                ],
                'write' => ['report_filename' => $this->sourceFilename],
            ]
        );

        $this->assertFileEquals($this->fixtureDir . 'expected/framework-dependencies.csv', $this->sourceFilename);
    }

    public function testBuildWithoutDependencies()
    {
        $this->builder->build(
            [
                'parse' => [
                    'files_for_parse' => [$this->fixtureDirModule . 'Model/WithoutDependencies.php'],
                    'config_files' => [$this->fixtureDirModule . 'etc/module.xml'],
                    'declared_namespaces' => ['Magento'],
                ],
                'write' => ['report_filename' => $this->sourceFilename],
            ]
        );

        $this->assertFileEquals(
            $this->fixtureDir . 'expected/without-framework-dependencies.csv',
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
