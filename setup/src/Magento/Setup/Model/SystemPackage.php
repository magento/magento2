<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    const EDITION_COMMUNITY = 'magento/product-community-edition';

    const EDITION_ENTERPRISE = 'magento/product-enterprise-edition';

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
     * @return array
     * @throws \RuntimeException
     */
    public function getPackageVersions()
    {
        $currentCE = '0';

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
        return $versions;
    }

    /**
     * Get installed system packages.
     *
     * @return array
     * @throws \Exception
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
                '<a href="https://devdocs.magento.com/guides/v2.3/install-gde/install/cli/dev_options.html">' .
                'Installation Guide</a>.'
            );
        }
        return $systemPackages;
    }

    /**
     * Sort versions.
     *
     * @param array $enterpriseVersions
     * @return array
     */
    public function sortVersions($enterpriseVersions)
    {
        usort(
            $enterpriseVersions[InfoCommand::AVAILABLE_VERSIONS],
            function ($versionOne, $versionTwo) {
                if (version_compare($versionOne, $versionTwo, '==')) {
                    return 0;
                }
                return (version_compare($versionOne, $versionTwo, '<')) ? 1 : -1;
            }
        );

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

        usort(
            $versions,
            function ($versionOne, $versionTwo) {
                if (version_compare($versionOne['id'], $versionTwo['id'], '==')) {
                    if ($versionOne['package'] === static::EDITION_COMMUNITY) {
                        return 1;
                    }
                    return 0;
                }
                return (version_compare($versionOne['id'], $versionTwo['id'], '<')) ? 1 : -1;
            }
        );

        return $versions;
    }

    /**
     * Filter enterprise versions.
     *
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
}
