<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Composer\Console\Application;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * A class to check if there are any dependency to package(s) that exists in the codebase, regardless of package type
 */
class DependencyChecker
{
    /**
     * @var Application
     */
    private $composerApp;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param Application $composerApp
     * @param DirectoryList $directoryList
     */
    public function __construct(Application $composerApp, DirectoryList $directoryList)
    {
        $this->composerApp = $composerApp;
        $this->directoryList = $directoryList;
    }

    /**
     * Checks dependencies to package(s), returns array of dependencies in the format of
     * 'package A' => [array of package names depending on package A]
     * If $excludeSelf is set to true, items in $packages will be excluded in all
     * "array of package names depending on package A"
     *
     * @param string[] $packages
     * @param bool $excludeSelf
     * @return string[]
     */
    public function checkDependencies(array $packages, $excludeSelf = false)
    {
        $this->composerApp->setAutoExit(false);
        $dependencies = [];
        foreach ($packages as $package) {
            $buffer = new BufferedOutput();
            $this->composerApp->resetComposer();
            $this->composerApp->run(
                new ArrayInput(
                    ['command' => 'depends', '--working-dir' => $this->directoryList->getRoot(), 'package' => $package]
                ),
                $buffer
            );
            $dependingPackages = $this->parseComposerOutput($buffer->fetch());
            if ($excludeSelf === true) {
                $dependingPackages = array_values(array_diff($dependingPackages, $packages));
            }
            $dependencies[$package] = $dependingPackages;
        }
        return $dependencies;
    }

    /**
     * Parse output from running composer remove command into an array of depending packages
     *
     * @param string $output
     * @return string[]
     */
    private function parseComposerOutput($output)
    {
        $rawLines = explode(PHP_EOL, $output);
        $packages = [];
        foreach ($rawLines as $rawLine) {
            $parts = explode(' ', $rawLine);
            if (count(explode('/', $parts[0])) == 2) {
                if (strpos($parts[0], 'magento/project-') === false) {
                    $packages[] = $parts[0];
                }
            }
        }
        return $packages;
    }
}
