<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Composer\ComposerInformation;

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
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var array
     */
    private $packagesJson;

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
     * @var array
     */
    private $metapackagesMap;

    /**
     * PackagesData constructor.
     *
     * @param ComposerInformation $composerInformation,
     * @param \Magento\Setup\Model\DateTime\TimeZoneProvider $timeZoneProvider,
     * @param \Magento\Setup\Model\PackagesAuth $packagesAuth,
     * @param \Magento\Framework\Filesystem $filesystem,
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ComposerInformation $composerInformation,
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
            $lastSyncData = [];
            $lastSyncData['lastSyncDate'] = $this->getLastSyncDate();
            $lastSyncData['packages'] = $this->getPackagesForUpdate();
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
                \IntlDateFormatter::NONE,
                null,
                null,
                'd MMM Y'
            ),
            'time' => $timezone->formatDateTime(
                new \DateTime('@'.$syncDate),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::MEDIUM,
                null,
                null,
                'hh:mma'
            ),
        ];
    }

    /**
     * Get list of manually installed package
     *
     * @return array
     */
    public function getInstalledPackages()
    {
        return array_intersect_key(
            $this->composerInformation->getInstalledMagentoPackages(),
            $this->composerInformation->getRootPackage()->getRequires()
        );
    }

    /**
     * Get packages that need updates
     *
     * @return array
     */
    public function getPackagesForUpdate()
    {
        $packagesForUpdate = [];
        $packages = $this->getInstalledPackages();

        foreach ($packages as $package) {
            $latestProductVersion = $this->getLatestNonDevVersion($package['name']);
            if ($latestProductVersion && version_compare($latestProductVersion, $package['version'], '>')) {
                $availableVersions = $this->getPackageAvailableVersions($package['name']);
                $package['latestVersion'] = $latestProductVersion;
                $package['versions'] = array_filter($availableVersions, function ($version) use ($package) {
                    return version_compare($version, $package['version'], '>');
                });
                $packagesForUpdate[$package['name']] = $package;
            }
        }
        
        return $packagesForUpdate;
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
     * Gets array of packages from packages.json
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getPackagesJson()
    {
        if ($this->packagesJson !== null) {
            return $this->packagesJson;
        }

        try {
            $jsonData = '';
            $directory = $this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::COMPOSER_HOME
            );
            if ($directory->isExist(PackagesAuth::PATH_TO_PACKAGES_FILE)) {
                $jsonData = $directory->readFile(PackagesAuth::PATH_TO_PACKAGES_FILE);
            }
            $packagesData = json_decode($jsonData, true);

            $this->packagesJson = isset($packagesData['packages']) ?
                $packagesData['packages'] :
                [];

            return $this->packagesJson;
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
            $packages = $this->composerInformation->getInstalledMagentoPackages();
            $packageNames = array_column($packages, 'name');
            $installPackages = [];
            foreach ($packagesJson as $packageName => $package) {
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
        $actualInstallPackages = [];

        try {
            $installPackages = $this->syncPackagesForInstall()['packages'];
            $metaPackageByPackage = $this->getMetaPackageForPackage($installPackages);
            foreach ($installPackages as $package) {
                $package['metapackage'] =
                    isset($metaPackageByPackage[$package['name']]) ? $metaPackageByPackage[$package['name']] : '';
                $actualInstallPackages[$package['name']] = $package;
                $actualInstallPackages[$package['name']]['version'] = $package['versions'][0];
            }
            $installPackagesInfo['packages'] = $actualInstallPackages;
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
            if ($package['type'] == ComposerInformation::METAPACKAGE_PACKAGE_TYPE) {
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
     * Get all metapackages
     *
     * @return array
     */
    public function getMetaPackagesMap()
    {
        if ($this->metapackagesMap === null) {
            $packages = $this->getPackagesJson();
            array_walk($packages, function ($packageVersions) {
                $package = array_shift($packageVersions);
                if ($package['type'] == ComposerInformation::METAPACKAGE_PACKAGE_TYPE
                    && isset($package['require'])
                ) {
                    foreach (array_keys($package['require']) as $key) {
                        $this->metapackagesMap[$key] = $package['name'];
                    }
                }
            });
        }

        return $this->metapackagesMap;
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
            $packagesJson = $this->getPackagesJson();

            if (isset($packagesJson[$package])) {
                $packageVersions = $packagesJson[$package];
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
                ->get(\Magento\Framework\Composer\MagentoComposerApplicationFactory::class);
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
