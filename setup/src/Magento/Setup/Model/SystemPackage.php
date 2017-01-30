<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Composer\ComposerInformation;

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
        $result = [];
        $systemPackages = [];
        $systemPackages = $this->getInstalledSystemPackages($systemPackages);
        foreach ($systemPackages as $systemPackage) {
            $versions = [];
            $systemPackageInfo = $this->infoCommand->run($systemPackage);
            if (!$systemPackageInfo) {
                throw new \RuntimeException("We cannot retrieve information on $systemPackage.");
            }

            $versions = $this->getSystemPackageVersions($systemPackageInfo, $versions);

            if ($systemPackageInfo['name'] == 'magento/product-community-edition') {
                $currentCE = $systemPackageInfo[InfoCommand::CURRENT_VERSION];
            }
            if (count($versions) > 1) {
                $versions[0]['name'] .= ' (latest)';
            }

            if (count($versions) >= 1) {
                $versions[count($versions) - 1]['name'] .= ' (current)';
            }

            $result[] = [
                'package' => $systemPackageInfo['name'],
                'versions' => $versions
            ];
        }

        if (!in_array('magento/product-enterprise-edition', $systemPackages)) {
            $result = array_merge($this->getAllowedEnterpriseVersions($currentCE), $result);
        }
        
        $result = $this->formatPackages($result);

        return $result;
    }

    /**
     * @param string $currentCE
     * @return array
     */
    public function getAllowedEnterpriseVersions($currentCE)
    {
        $result = [];
        $enterpriseVersions = $this->infoCommand->run('magento/product-enterprise-edition');
        $eeVersions = [];
        $maxVersion = '';
        if (is_array($enterpriseVersions) && array_key_exists('available_versions', $enterpriseVersions)) {
            $enterpriseVersions = $this->sortVersions($enterpriseVersions);
            if (isset($enterpriseVersions['available_versions'][0])) {
                $maxVersion = $enterpriseVersions['available_versions'][0];
            }
            $eeVersions = $this->filterEeVersions($currentCE, $enterpriseVersions, $maxVersion);
        }

        if (!empty($eeVersions)) {
            $result[] = [
                'package' => 'magento/product-enterprise-edition',
                'versions' => $eeVersions
            ];
        }
        return $result;
    }

    /**
     * @param array $systemPackageInfo
     * @param array $versions
     * @return array
     */
    public function getSystemPackageVersions($systemPackageInfo, $versions)
    {
        $editionType = '';
        if ($systemPackageInfo['name'] == 'magento/product-community-edition') {
            $editionType .= 'CE';
        } else if ($systemPackageInfo['name'] == 'magento/product-enterprise-edition') {
            $editionType .= 'EE';
        }
        foreach ($systemPackageInfo[InfoCommand::NEW_VERSIONS] as $version) {
            $versions[] = ['id' => $version, 'name' => 'Version ' . $version . ' ' . $editionType];
        }

        if ($systemPackageInfo[InfoCommand::CURRENT_VERSION]) {
            $versions[] = [
                'id' => $systemPackageInfo[InfoCommand::CURRENT_VERSION],
                'name' => 'Version ' . $systemPackageInfo[InfoCommand::CURRENT_VERSION] . ' ' . $editionType
            ];
        }
        return  $versions;
    }

    /**
     * @param array $systemPackages
     * @return array
     * @throws \RuntimeException
     */
    public function getInstalledSystemPackages($systemPackages)
    {
        $systemPackages = [];
        $locker = $this->magentoComposerApplication->createComposer()->getLocker();

        /** @var \Composer\Package\CompletePackage $package */
        foreach ($locker->getLockedRepository()->getPackages() as $package) {
            $packageName = $package->getName();
            if ($this->composerInfo->isSystemPackage($packageName)) {
                if ($packageName == 'magento/product-community-edition') {
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
        usort($enterpriseVersions['available_versions'], function ($versionOne, $versionTwo) {
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

                if (preg_match('/^[0-9].[0-9].[0-9]$/', $version['id']) || strpos($version['name'], 'current')) {
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
                if ($versionOne['package'] === 'magento/product-community-edition') {
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
        foreach ($enterpriseVersions['available_versions'] as $version) {
            $requires = $this->composerInfo->getPackageRequirements('magento/product-enterprise-edition', $version);
            if (array_key_exists('magento/product-community-edition', $requires)) {
                /** @var \Composer\Package\Link $ceRequire */
                $ceRequire = $requires['magento/product-community-edition'];
                if (version_compare(
                    $ceRequire->getConstraint()->getPrettyString(),
                    $currentCE,
                    '>='
                )) {
                    $name = 'Version ' . $version . ' EE';
                    if ($maxVersion == $version) {
                        $name .= ' (latest)';
                    }
                    $eeVersions[] = ['id' => $version, 'name' => $name];
                }
            }
        }
        return $eeVersions;
    }
}
