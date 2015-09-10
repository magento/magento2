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

    /** @var string */
    protected $toolsTests = '#dev/tools/Magento/Tools/[\\w]+/Test#';

    /** @var string */
    protected $frameworkTests = '#lib/internal/Magento/Framework/[\\w]+/Test#';

    /** @var string */
    protected $libTests = '#lib/internal/[\\w]+/[\\w]+/Test#';

    /** @var string */
    protected $rootTestsDir = '#dev/tests/#';

    /** @var string */
    protected $setupTestsDir = '#setup/src/Magento/Setup/Test#';

    public function setUp()
    {
        $this->model = new Files(new ComponentRegistrar, BP);
        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $this->moduleTests[] = '#' . $moduleDir . '/Test#';
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
            $this->model->getComposerFiles('module', false)
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
        $classFiles = preg_grep($this->libTests, $classFiles, PREG_GREP_INVERT);
        $classFiles = preg_grep($this->frameworkTests, $classFiles, PREG_GREP_INVERT);
        $classFiles = preg_grep($this->toolsTests, $classFiles, PREG_GREP_INVERT);
        $classFiles = preg_grep($this->rootTestsDir, $classFiles, PREG_GREP_INVERT);
        $classFiles = preg_grep($this->setupTestsDir, $classFiles, PREG_GREP_INVERT);

        $this->assertEmpty($classFiles);
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
        $this->assertEmpty(preg_grep($this->frameworkTests, $files));
        $this->assertEmpty(preg_grep($this->libTests, $files));
        $this->assertEmpty(preg_grep($this->toolsTests, $files));
    }
}
