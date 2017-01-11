<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Composer\InfoCommand;
use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;

/**
 * Class SystemPackage returns system package and available for update versions
 */
class SystemPackage
{
    /**
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * @var MagentoComposerApplication
     */
    private $magentoComposerApplication;

    /**
     * @var ComposerInformation
     */
    private $composerInfo;

    /**#@+
     * Constants for different Magento editions
     */
    const EDITION_COMMUNITY = 'magento/product-community-edition';
    const EDITION_ENTERPRISE = 'magento/product-enterprise-edition';
    const EDITION_B2B = 'magento/product-b2b-edition';
    /**#@-*/

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerAppFactory
     * @param ComposerInformation $composerInfo
     */
    public function __construct(
        MagentoComposerApplicationFactory $composerAppFactory,
        ComposerInformation $composerInfo
    ) {
        $this->infoCommand = $composerAppFactory->createInfoCommand();
        $this->magentoComposerApplication = $composerAppFactory->create();
        $this->composerInfo = $composerInfo;
    }

    /**
     * Returns system package and available versions
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getPackageVersions()
    {
        $currentCE = '0';
        $currentEE = $currentCE;

        $result = [];
        $systemPackages = $this->getInstalledSystemPackages();
        foreach ($systemPackages as $systemPackage) {
            $systemPackageInfo = $this->infoCommand->run($systemPackage);
            if (!$systemPackageInfo) {
                throw new \RuntimeException("We cannot retrieve information on $systemPackage.");
            }

            $versions = $this->getSystemPackageVersions($systemPackageInfo);

            if ($systemPackageInfo['name'] == static::EDITION_COMMUNITY) {
                $currentCE = $systemPackageInfo[InfoCommand::CURRENT_VERSION];
            }

            if ($systemPackageInfo['name'] == static::EDITION_ENTERPRISE) {
                $currentEE = $systemPackageInfo[InfoCommand::CURRENT_VERSION];
            }

            if (count($versions) > 1) {
                $versions[0]['name'] .= ' (latest)';
            }

            $result[] = [
                'package' => $systemPackageInfo['name'],
                'versions' => $versions,
            ];
        }

        if (!in_array(static::EDITION_ENTERPRISE, $systemPackages)) {
            $result = array_merge($this->getAllowedEnterpriseVersions($currentCE), $result);
        }

        if (
            in_array(static::EDITION_ENTERPRISE, $systemPackages)
            && !in_array(static::EDITION_B2B, $systemPackages)
        ) {
            $result = array_merge($this->getAllowedB2BVersions($currentEE), $result);
        }

        $result = $this->formatPackages($result);

        return $result;
    }

    /**
     * Retrieve allowed EE versions
     *
     * @param string $currentCE
     * @return array
     */
    public function getAllowedEnterpriseVersions($currentCE)
    {
        $result = [];
        $enterpriseVersions = $this->infoCommand->run(static::EDITION_ENTERPRISE);
        $eeVersions = [];
        $maxVersion = '';
        if (is_array($enterpriseVersions) && array_key_exists(InfoCommand::AVAILABLE_VERSIONS, $enterpriseVersions)) {
            $enterpriseVersions = $this->sortVersions($enterpriseVersions);
            if (isset($enterpriseVersions[InfoCommand::AVAILABLE_VERSIONS][0])) {
                $maxVersion = $enterpriseVersions[InfoCommand::AVAILABLE_VERSIONS][0];
            }
            $eeVersions = $this->filterEeVersions($currentCE, $enterpriseVersions, $maxVersion);
        }

        if (!empty($eeVersions)) {
            $result[] = [
                'package' => static::EDITION_ENTERPRISE,
                'versions' => $eeVersions,
            ];
        }
        return $result;
    }

    /**
     * Retrieve allowed B2B versions
     *
     * @param string $currentEE
     * @return array
     */
    public function getAllowedB2BVersions($currentEE)
    {
        $result = [];
        $versions = $this->fetchInfoVersions(static::EDITION_B2B);
        $versionsPrepared = [];
        $maxVersion = '';

        if ($versions[InfoCommand::AVAILABLE_VERSIONS]) {
            $versions = $this->sortVersions($versions);
            if (isset($versions[InfoCommand::AVAILABLE_VERSIONS][0])) {
                $maxVersion = $versions[InfoCommand::AVAILABLE_VERSIONS][0];
            }
            $versionsPrepared = $this->filterB2bVersions($currentEE, $versions, $maxVersion);
        }

        if ($versionsPrepared) {
            $result[] = [
                'package' => static::EDITION_B2B,
                'versions' => $versionsPrepared,
            ];
        }

        return $result;
    }

    /**
     * Fetching of info command response to display all correct versions
     *
     * @param string $command
     * @return array
     */
    private function fetchInfoVersions($command)
    {
        $versions = (array)$this->infoCommand->run($command);

        $versions[InfoCommand::CURRENT_VERSION] = isset($versions[InfoCommand::CURRENT_VERSION])
            ? $versions[InfoCommand::CURRENT_VERSION]
            : null;
        $versions[InfoCommand::AVAILABLE_VERSIONS] = isset($versions[InfoCommand::AVAILABLE_VERSIONS])
            ? $versions[InfoCommand::AVAILABLE_VERSIONS]
            : null;
        $versions[InfoCommand::AVAILABLE_VERSIONS] = array_unique(
            array_merge(
                (array)$versions[InfoCommand::CURRENT_VERSION],
                (array)$versions[InfoCommand::AVAILABLE_VERSIONS]
            )
        );

        return $versions;
    }

    /**
     * Retrieve package versions
     *
     * @param array $systemPackageInfo
     * @return array
     */
    public function getSystemPackageVersions($systemPackageInfo)
    {
        $editionType = '';
        $versions = [];

        if ($systemPackageInfo['name'] == static::EDITION_COMMUNITY) {
            $editionType .= 'CE';
        } elseif ($systemPackageInfo['name'] == static::EDITION_ENTERPRISE) {
            $editionType .= 'EE';
        } elseif ($systemPackageInfo['name'] == static::EDITION_B2B) {
            $editionType .= 'B2B';
        }

        foreach ($systemPackageInfo[InfoCommand::NEW_VERSIONS] as $version) {
            $versions[] = ['id' => $version, 'name' => 'Version ' . $version . ' ' . $editionType, 'current' => false];
        }

        if ($systemPackageInfo[InfoCommand::CURRENT_VERSION]) {
            $versions[] = [
                'id' => $systemPackageInfo[InfoCommand::CURRENT_VERSION],
                'name' => 'Version ' . $systemPackageInfo[InfoCommand::CURRENT_VERSION] . ' ' . $editionType,
                'current' => true,
            ];
        }
        return  $versions;
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function getInstalledSystemPackages()
    {
        $locker = $this->magentoComposerApplication->createComposer()->getLocker();

        /** @var \Composer\Package\CompletePackage $package */
        foreach ($locker->getLockedRepository()->getPackages() as $package) {
            $packageName = $package->getName();
            if ($this->composerInfo->isSystemPackage($packageName)) {
                if ($packageName == static::EDITION_COMMUNITY) {
                    if ($this->composerInfo->isPackageInComposerJson($packageName)) {
                        $systemPackages[] = $packageName;
                    }
                } else {
                    $systemPackages[] = $packageName;
                }
            }
        }
        if (empty($systemPackages)) {
            throw new \RuntimeException(
                'We\'re sorry, no components are available because you cloned the Magento 2 GitHub repository. ' .
                'You must manually update components as discussed in the ' .
                '<a href="http://devdocs.magento.com/guides/v2.0/install-gde/install/cli/dev_options.html">' .
                'Installation Guide</a>.'
            );
        }
        return $systemPackages;
    }

    /**
     * @param array $enterpriseVersions
     * @return array
     */
    public function sortVersions($enterpriseVersions)
    {
        usort($enterpriseVersions[InfoCommand::AVAILABLE_VERSIONS], function ($versionOne, $versionTwo) {
            if (version_compare($versionOne, $versionTwo, '==')) {
                return 0;
            }
            return (version_compare($versionOne, $versionTwo, '<')) ? 1 : -1;
        });

        return $enterpriseVersions;
    }

    /**
     * Re-formats packages array to merge packages, sort versions and add technical data
     *
     * @param array $packages
     * @return array
     */
    private function formatPackages($packages)
    {
        $versions = [];

        foreach ($packages as $package) {
            foreach ($package['versions'] as $version) {
                $version['package'] = $package['package'];

                if (preg_match('/^[0-9].[0-9].[0-9]$/', $version['id']) || $version['current']) {
                    $version['stable'] = true;
                } else {
                    $version['name'] = $version['name'] . ' (unstable version)';
                    $version['stable'] = false;
                }

                $versions[] = $version;
            }
        }

        usort($versions, function ($versionOne, $versionTwo) {
            if (version_compare($versionOne['id'], $versionTwo['id'], '==')) {
                if ($versionOne['package'] === static::EDITION_COMMUNITY) {
                    return 1;
                }
                return 0;
            }
            return (version_compare($versionOne['id'], $versionTwo['id'], '<')) ? 1 : -1;
        });

        return $versions;
    }

    /**
     * @param string $currentCE
     * @param array $enterpriseVersions
     * @param string $maxVersion
     * @return array
     */
    public function filterEeVersions($currentCE, $enterpriseVersions, $maxVersion)
    {
        $eeVersions = [];
        foreach ($enterpriseVersions[InfoCommand::AVAILABLE_VERSIONS] as $version) {
            $requires = $this->composerInfo->getPackageRequirements(static::EDITION_ENTERPRISE, $version);
            if (array_key_exists(static::EDITION_COMMUNITY, $requires)) {
                /** @var \Composer\Package\Link $ceRequire */
                $ceRequire = $requires[static::EDITION_COMMUNITY];
                if (version_compare(
                    $ceRequire->getConstraint()->getPrettyString(),
                    $currentCE,
                    '>='
                )) {
                    $name = 'Version ' . $version . ' EE';
                    if ($maxVersion == $version) {
                        $name .= ' (latest)';
                    }
                    $eeVersions[] = ['id' => $version, 'name' => $name, 'current' => false];
                }
            }
        }
        return $eeVersions;
    }

    /**
     * Filtering B2B versions
     *
     * @param string $currentEE
     * @param array $b2bVersions
     * @param string $maxVersion
     * @return array
     */
    public function filterB2bVersions($currentEE, $b2bVersions, $maxVersion)
    {
        $b2bVersionsPrepared = [];
        foreach ($b2bVersions[InfoCommand::AVAILABLE_VERSIONS] as $version) {
            $requires = $this->composerInfo->getPackageRequirements(static::EDITION_B2B, $version);
            if (array_key_exists(static::EDITION_ENTERPRISE, $requires)) {
                /** @var \Composer\Package\Link $eeRequire */
                $eeRequire = $requires[static::EDITION_ENTERPRISE];
                if (version_compare(
                    $eeRequire->getConstraint()->getPrettyString(),
                    $currentEE,
                    '>='
                )) {
                    $name = 'Version ' . $version . ' B2B';
                    if ($maxVersion == $version) {
                        $name .= ' (latest)';
                    }
                    $b2bVersionsPrepared[] = ['id' => $version, 'name' => $name, 'current' => false];
                }
            }
        }
        return $b2bVersionsPrepared;
    }
}
