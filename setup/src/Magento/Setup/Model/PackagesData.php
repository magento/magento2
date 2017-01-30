<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Class PackagesData returns system packages and available for update versions
 */
class PackagesData
{
    /**#@+
     * Composer command params and options
     */
    const COMPOSER_SHOW = 'show';
    const PARAM_COMMAND = 'command';
    const PARAM_PACKAGE = 'package';
    const PARAM_AVAILABLE = '--available';
    /**#@-*/

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\PackagesAuth
     */
    private $packagesAuth;

    /**
     * @var \Magento\Setup\Model\DateTime\TimeZoneProvider
     */
    private $timeZoneProvider;

    /**
     * @var  \Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * PackagesData constructor.
     *
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation,
     * @param \Magento\Setup\Model\DateTime\TimeZoneProvider $timeZoneProvider,
     * @param \Magento\Setup\Model\PackagesAuth $packagesAuth,
     * @param \Magento\Framework\Filesystem $filesystem,
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Setup\Model\DateTime\TimeZoneProvider $timeZoneProvider,
        \Magento\Setup\Model\PackagesAuth $packagesAuth,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->composerInformation = $composerInformation;
        $this->timeZoneProvider = $timeZoneProvider;
        $this->packagesAuth = $packagesAuth;
        $this->filesystem = $filesystem;
    }

    /**
     * @return array|bool|mixed
     * @throws \RuntimeException
     */
    public function syncPackagesData()
    {
        try {
            $lastSyncData = $this->syncPackagesForUpdate();
            $packagesForInstall = $this->syncPackagesForInstall();
            $lastSyncData = $this->formatLastSyncData($packagesForInstall, $lastSyncData);
            return $lastSyncData;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Gets last sync date
     *
     * @return string
     */
    private function getLastSyncDate()
    {
        $directory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::COMPOSER_HOME
        );
        if ($directory->isExist(PackagesAuth::PATH_TO_PACKAGES_FILE)) {
            $fileData = $directory->stat(PackagesAuth::PATH_TO_PACKAGES_FILE);
            return $fileData['mtime'];
        }
        return '';
    }

    /**
     * Format the lastSyncData for use on frontend
     *
     * @param array $packagesForInstall
     * @param array $lastSyncData
     * @return mixed
     */
    private function formatLastSyncData($packagesForInstall, $lastSyncData)
    {
        $lastSyncData['countOfInstall']
            = isset($packagesForInstall['packages']) ? count($packagesForInstall['packages']) : 0;
        $lastSyncData['countOfUpdate'] = isset($lastSyncData['packages']) ? count($lastSyncData['packages']) : 0;
        $lastSyncData['installPackages'] = $packagesForInstall['packages'];
        if (isset($lastSyncData['lastSyncDate'])) {
            $lastSyncData['lastSyncDate'] = $this->formatSyncDate($lastSyncData['lastSyncDate']);
        }
        return $lastSyncData;
    }

    /**
     * Format a UTC timestamp (seconds since epoch) to structure expected by frontend
     *
     * @param string $syncDate seconds since epoch
     * @return array
     */
    private function formatSyncDate($syncDate)
    {
        $timezone = $this->timeZoneProvider->get();
        return [
            'date' => $timezone->formatDateTime(
                new \DateTime('@'.$syncDate),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE
            ),
            'time' => $timezone->formatDateTime(
                new \DateTime('@'.$syncDate),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::MEDIUM
            ),
        ];
    }

    /**
     * Sync packages that need updates
     *
     * @return array
     */
    private function syncPackagesForUpdate()
    {
        $availableVersions = [];
        $packages = $this->composerInformation->getInstalledMagentoPackages();
        foreach ($packages as $package) {
            $latestProductVersion = $this->getLatestNonDevVersion($package['name']);
            if ($latestProductVersion && version_compare($latestProductVersion, $package['version'], '>')) {
                $packageName = $package['name'];
                $availableVersions[$packageName] = [
                    'name' => $packageName,
                    'latestVersion' => $latestProductVersion
                ];
            }
        }
        $lastSyncData['lastSyncDate'] = $this->getLastSyncDate();
        $lastSyncData['packages'] = $availableVersions;
        return $lastSyncData;
    }

    /**
     * Retrieve the latest available stable version for a package
     *
     * @param string $package
     * @return string
     */
    private function getLatestNonDevVersion($package)
    {
        $versionParser = new \Composer\Package\Version\VersionParser();
        foreach ($this->getPackageAvailableVersions($package) as $version) {
            if ($versionParser->parseStability($version) != 'dev') {
                return $version;
            }
        }
        return '';
    }

    /**
     * Gets packages.json
     *
     * @return string
     * @throws \RuntimeException
     */
    private function getPackagesJson()
    {
        try {
            $packagesJson = '';
            $directory = $this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::COMPOSER_HOME
            );
            if ($directory->isExist(PackagesAuth::PATH_TO_PACKAGES_FILE)) {
                $packagesJson = $directory->readFile(PackagesAuth::PATH_TO_PACKAGES_FILE);
            }
            return $packagesJson;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error in reading packages.json');
        }
    }

    /**
     * Sync packages for install
     *
     * @return array
     * @throws \RuntimeException
     */
    private function syncPackagesForInstall()
    {
        try {
            $packagesJson = $this->getPackagesJson();
            if ($packagesJson) {
                $packagesJsonData = json_decode($packagesJson, true);
            } else {
                $packagesJsonData['packages'] = [];
            }
            $packages = $this->composerInformation->getInstalledMagentoPackages();
            $packageNames = array_column($packages, 'name');
            $installPackages = [];
            foreach ($packagesJsonData['packages'] as $packageName => $package) {
                if (!empty($package) && isset($package) && is_array($package)) {
                    $package = $this->unsetDevVersions($package);
                    ksort($package);
                    $packageValues = array_values($package);
                    if ($this->isNewUserPackage($packageValues[0], $packageNames)) {
                        $versions = array_reverse(array_keys($package));
                        $installPackage = $packageValues[0];
                        $installPackage['versions'] = $versions;
                        $installPackage['name'] = $packageName;
                        $installPackage['vendor'] = explode('/', $packageName)[0];
                        $installPackages[$packageName] = $installPackage;
                    }
                }
            }
            $packagesForInstall['packages'] = $installPackages;
            return $packagesForInstall;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error in syncing packages for Install');
        }
    }

    /**
     * Check if this new user package
     *
     * @param array $package
     * @param array $packageNames
     * @return bool
     */
    protected function isNewUserPackage($package, $packageNames)
    {
        if (!in_array($package['name'], $packageNames) &&
            in_array($package['type'], $this->composerInformation->getPackagesTypes()) &&
            strpos($package['name'], 'magento/product-') === false &&
            strpos($package['name'], 'magento/project-') === false
        ) {
            return true;
        }
        return false;
    }

    /**
     * Unset dev versions
     *
     * @param array $package
     * @return array
     */
    protected function unsetDevVersions($package)
    {
        foreach ($package as $key => $version) {
            if (strpos($key, 'dev') !== false) {
                unset($package[$key]);
            }
        }
        unset($version);

        return $package;
    }

    /**
     * Sync list of available for install versions for packages
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getPackagesForInstall()
    {
        $actualInstallackages = [];
        $installPackagesInfo = $this->syncPackagesForInstall();

        try {
            $installPackages = $installPackagesInfo['packages'];
            $availablePackageNames = array_column(
                $this->composerInformation->getInstalledMagentoPackages(),
                'name'
            );
            $metaPackageByPackage = $this->getMetaPackageForPackage($installPackages);
            foreach ($installPackages as $package) {
                if (!in_array($package['name'], $availablePackageNames) &&
                    in_array($package['type'], $this->composerInformation->getPackagesTypes()) &&
                    strpos($package['name'], 'magento/product-') === false &&
                    strpos($package['name'], 'magento/project-') === false
                ) {
                    $package['metapackage'] =
                        isset($metaPackageByPackage[$package['name']]) ? $metaPackageByPackage[$package['name']] : '';
                    $actualInstallackages[$package['name']] = $package;
                    $actualInstallackages[$package['name']]['version'] = $package['versions'][0];
                }
            }
            $installPackagesInfo['packages'] = $actualInstallackages;
            return $installPackagesInfo;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error in getting new packages to install');
        }
    }

    /**
     *
     * @param array $packages
     * @return array
     */
    private function getMetaPackageForPackage($packages)
    {
        $result = [];
        foreach ($packages as $package) {
            if ($package['type'] == \Magento\Framework\Composer\ComposerInformation::METAPACKAGE_PACKAGE_TYPE) {
                if (isset($package['require'])) {
                    foreach ($package['require'] as $key => $requirePackage) {
                        $result[$key] = $package['name'];
                    }
                }
            }
        }
        unset($requirePackage);

        return $result;
    }

    /**
     * Retrieve all available versions for a package
     *
     * @param string $package
     * @return array
     * @throws \RuntimeException
     */
    private function getPackageAvailableVersions($package)
    {
        $magentoRepositories = $this->composerInformation->getRootRepositories();

        // Check we have only one repo.magento.com repository
        if (
            count($magentoRepositories) === 1
            && strpos($magentoRepositories[0], $this->packagesAuth->getCredentialBaseUrl())
        ) {
            $packagesJsonData = $this->getPackagesJson();
            if ($packagesJsonData) {
                $packagesJsonData = json_decode($packagesJsonData, true);
            } else {
                $packagesJsonData['packages'] = [];
            }

            if (isset($packagesJsonData['packages'][$package])) {
                $packageVersions = $packagesJsonData['packages'][$package];
                uksort($packageVersions, 'version_compare');
                $packageVersions = array_reverse($packageVersions);

                return array_keys($packageVersions);
            }
        } else {
            $versionsPattern = '/^versions\s*\:\s(.+)$/m';

            $commandParams = [
                self::PARAM_COMMAND => self::COMPOSER_SHOW,
                self::PARAM_PACKAGE => $package,
                self::PARAM_AVAILABLE => true
            ];

            $applicationFactory = $this->objectManagerProvider->get()
                ->get('Magento\Framework\Composer\MagentoComposerApplicationFactory');
            /** @var \Magento\Composer\MagentoComposerApplication $application */
            $application = $applicationFactory->create();

            $result = $application->runComposerCommand($commandParams);
            $matches = [];
            preg_match($versionsPattern, $result, $matches);
            if (isset($matches[1])) {
                return explode(', ', $matches[1]);
            }
        }

        throw new \RuntimeException(
            sprintf('Couldn\'t get available versions for package %s', $package)
        );
    }
}
