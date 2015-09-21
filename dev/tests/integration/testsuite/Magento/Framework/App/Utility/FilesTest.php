<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

class FilesTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\App\Utility\Files */
    protected $model;

    /** @var array */
    protected $moduleTests = [];

    /** @var array */
    protected $frameworkTests = [];

    /** @var array */
    protected $libTests = [];

    /** @var string */
    protected $rootTestsDir = '#dev/tests/#';

    /** @var string */
    protected $setupTestsDir = '#setup/src/Magento/Setup/Test#';

    public function setUp()
    {
        $componentRegistrar = new ComponentRegistrar();
        $dirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Component\DirSearch');
        $this->model = new Files($componentRegistrar, $dirSearch, BP);
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $this->moduleTests[] = '#' . $moduleDir . '/Test#';
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
            $this->libTests[] = '#' . $libraryDir . '/Test#';
            $this->frameworkTests[] = '#' . $libraryDir . '/[\\w]+/Test#';
        }
    }

    public function testGetPhpFilesExcludeTests()
    {
        $this->assertNoTestDirs(
            $this->model->getPhpFiles(true, true, true, false)
        );
    }

    public function testGetComposerExcludeTests()
    {
        $this->assertNoTestDirs(
            $this->model->getComposerFiles(ComponentRegistrar::MODULE, false)
        );
    }

    public function testGetClassFilesExcludeTests()
    {
        $this->assertNoTestDirs(
            $this->model->getClassFiles(true, false, true, true, false)
        );
    }

    public function testGetClassFilesOnlyTests()
    {
        $classFiles = $this->model->getClassFiles(false, true, false, false, false);

        foreach ($this->moduleTests as $moduleTest) {
            $classFiles = preg_grep($moduleTest, $classFiles, PREG_GREP_INVERT);
        }
        foreach ($this->libTests as $libraryTest) {
            $classFiles = preg_grep($libraryTest, $classFiles, PREG_GREP_INVERT);
        }
        foreach ($this->frameworkTests as $frameworkTest) {
            $classFiles = preg_grep($frameworkTest, $classFiles, PREG_GREP_INVERT);
        }

        $classFiles = preg_grep($this->rootTestsDir, $classFiles, PREG_GREP_INVERT);
        $classFiles = preg_grep($this->setupTestsDir, $classFiles, PREG_GREP_INVERT);

        $this->assertEmpty($classFiles);
    }

    public function testGetConfigFiles()
    {
        $actual = $this->model->getConfigFiles('*.xml');
        $this->assertNotEmpty($actual);
        foreach ($actual as $file) {
            $this->assertStringEndsWith('.xml', $file[0]);
        }
    }

    public function testGetLayoutConfigFiles()
    {
        $actual = $this->model->getLayoutConfigFiles('*.xml');
        $this->assertNotEmpty($actual);
        foreach ($actual as $file) {
            $this->assertStringEndsWith('.xml', $file[0]);
        }
    }

    /**
     * Verify that the given array of files does not contain anything in test directories
     *
     * @param array $files
     */
    protected function assertNoTestDirs($files)
    {
        foreach ($this->moduleTests as $moduleTest) {
            $this->assertEmpty(preg_grep($moduleTest, $files));
        }
        foreach ($this->frameworkTests as $frameworkTest) {
            $this->assertEmpty(preg_grep($frameworkTest, $files));
        }
        foreach ($this->libTests as $libTest) {
            $this->assertEmpty(preg_grep($libTest, $files));
        }
    }
}
