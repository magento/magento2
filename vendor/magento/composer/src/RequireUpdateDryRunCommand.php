<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer;

/**
 * Class RequireUpdateDryRunCommand calls composer require and update --dry-run commands
 */
class RequireUpdateDryRunCommand
{
    /**
     * @var MagentoComposerApplication
     */
    protected $magentoComposerApplication;

    /**
     * @var InfoCommand
     */
    protected $infoCommand;

    /**
     * Constructor
     *
     * @param MagentoComposerApplication $magentoComposerApplication
     * @param InfoCommand $infoCommand
     */
    public function __construct(
        MagentoComposerApplication $magentoComposerApplication,
        InfoCommand $infoCommand
    ) {
        $this->magentoComposerApplication = $magentoComposerApplication;
        $this->infoCommand = $infoCommand;
    }

    /**
     * Runs composer update --dry-run command
     *
     * @param array $packages
     * @param string|null $workingDir
     * @return string
     * @throws \RuntimeException
     */
    public function run($packages, $workingDir = null)
    {
        try {
            // run require
            $this->magentoComposerApplication->runComposerCommand(
                ['command' => 'require', 'packages' => $packages, '--no-update' => true],
                $workingDir
            );

            $output = $this->magentoComposerApplication->runComposerCommand(
                ['command' => 'update', '--dry-run' => true],
                $workingDir
            );
        } catch (\RuntimeException $e) {
            $errorMessage = $this->generateAdditionalErrorMessage($e->getMessage(), $packages);
            if ($errorMessage) {
                throw new \RuntimeException($errorMessage, $e->getCode(), $e);
            } else {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

        }

        return $output;
    }

    /**
     * Generates additional explanation for error message
     *
     * @param array $message
     * @param array $inputPackages
     * @return string
     */
    protected function generateAdditionalErrorMessage($message, $inputPackages)
    {
        $matches  = [];
        $errorMessage = '';
        $packages = [];
        $rawLines = explode(PHP_EOL, $message);

        foreach ($rawLines as $line) {
            if (preg_match('/- (.*) requires (.*) -> no matching package/', $line, $matches)) {
                $packages[] = $matches[1];
                $packages[] = $matches[2];
            }
        }

        if (!empty($packages)) {
            $packages = array_unique($packages);
            $packages = $this->explodePackagesAndVersions($packages);
            $inputPackages = $this->explodePackagesAndVersions($inputPackages);

            $update = [];
            $conflicts = [];

            foreach ($inputPackages as $package => $version) {
                if (isset($packages[$package])) {
                    $update[] = $package . ' to ' . $version;
                }
            }

            foreach (array_diff_key($packages, $inputPackages) as $package => $version) {

                if (!$packageInfo = $this->infoCommand->run($package, true)) {
                    return false;
                }

                $currentVersion = $packageInfo[InfoCommand::CURRENT_VERSION];

                if (empty($packageInfo[InfoCommand::AVAILABLE_VERSIONS])) {
                    $packageInfo = $this->infoCommand->run($package);
                    if (empty($packageInfo[InfoCommand::AVAILABLE_VERSIONS])) {
                        return false;
                    }
                }

                $conflicts[] = ' - ' . $package . ' version ' . $currentVersion . '. '
                    . 'Please try to update it to one of the following package versions: '
                    . implode(', ', $packageInfo['available_versions']);
            }

            $errorMessage = 'You are trying to update package(s) '
                . implode(', ', $update) . PHP_EOL
                . "We've detected conflicts with the following packages:" . PHP_EOL
                . implode(PHP_EOL, $conflicts)
                . PHP_EOL;
        }

        return $errorMessage;
    }

    /**
     * Returns array that contains package as key and version as value
     *
     * @param array $packages
     * @return array
     */
    protected function explodePackagesAndVersions($packages)
    {
        $packagesAndVersions = [];
        foreach ($packages as $package) {
            $package = explode(' ', $package);
            $packagesAndVersions[$package[0]] = $package[1];
        }

        return $packagesAndVersions;
    }
}
