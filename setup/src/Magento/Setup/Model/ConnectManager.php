<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ConnectManager
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    protected $composerInformation;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $pathToAuthFile = 'auth.json';

    /**#@+
     * Composer command params and options
     */
    const PARAM_COMMAND = 'command';
    const PARAM_KEY = 'setting-key';
    const PARAM_VALUE = 'setting-value';
    const PARAM_GLOBAL = '--global';
    const PARAM_CONFIG = 'config';
    const PARAM_HTTPBASIC = 'http-basic.';

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directory;

    /**
     * @var string
     */
    protected $pathToInstallPackagesCacheFile = 'install_composer_packages.json';

    /**
     * @var \Magento\Composer\MagentoComposerApplication
     */
    protected $application;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Composer\MagentoComposerApplicationFactory $applicationFactory
     */
    public function __construct(
        \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator,
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Composer\MagentoComposerApplicationFactory $applicationFactory
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->composerInformation = $composerInformation;
        $this->curlClient = $curl;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->application = $applicationFactory->create();
    }

    /**
     * @return string
     */
    public function getCheckCredentialUrl()
    {
        return $this->urlPrefix .  $this->getCredentialBaseUrl() .  '/check_credentials';
    }

    /**
     * @return string
     */
    public function getCredentialBaseUrl()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['connect']['check_credentials_url'];
    }

    /**
     * @return string
     */
    public function getPackagesJsonUrl()
    {
        return $this->urlPrefix .  $this->getCredentialBaseUrl() .  '/packages.json';
    }

    /**
     * @param string $token
     * @param string $secretKey
     * @return string
     */
    public function checkCredentialsAction($token, $secretKey)
    {
        $serviceUrl = $this->getCheckCredentialUrl();
        $this->getCurlClient()->setCredentials($token, $secretKey);
        try {
            $this->getCurlClient()->post($serviceUrl, []);
            return $this->getCurlClient()->getBody();
        } catch (\Exception $e) {
            return \Zend_Json::encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Gets packages.json
     *
     * @return bool|string
     */
    public function getPackagesJson()
    {
        $serviceUrl = $this->getPackagesJsonUrl();
        $authJsonData = $this->getAuthJsonData();
        if (!empty($authJsonData)) {
            $this->getCurlClient()->setCredentials($authJsonData['username'], $authJsonData['password']);
            try {
                $this->getCurlClient()->post($serviceUrl, []);
                return $this->getCurlClient()->getBody();
            } catch (\Exception $e) {
            }
        }
        return false;
    }

    /**
     * Sync packages for install
     *
     * @return bool
     */
    public function syncPackagesForInstall()
    {
        try {
            $packagesJson = $this->getPackagesJson();
            if ($packagesJson) {
                $packagesJsonData = json_decode($packagesJson, true);
            } else {
                $packagesJsonData['packages'] = [];
            }
            $packageNames = array_column($this->getComposerInformation()->getInstalledMagentoPackages(), 'name');
            $installPackages = [];
            foreach ($packagesJsonData['packages'] as $package) {
                ksort($package);
                $package = array_pop($package);
                if (!in_array($package['name'], $packageNames) &&
                    in_array($package['type'], $this->getComposerInformation()->getPackagesTypes())
                ) {
                    $installPackages[$package['name']] = $package;
                }
            }
            return $this->savePackagesForInstallToCache($installPackages) ? true : false;
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * Gets auth.json file
     *
     * @return array|false
     */
    public function getAuthJsonData()
    {
        try {
            $authJson = $this->getAuthJson();
            $serviceUrl = $this->getCredentialBaseUrl();
            $authJsonData = isset($authJson['http-basic'][$serviceUrl]) ? $authJson['http-basic'][$serviceUrl] : false;
        } catch (\Exception $e) {
            $authJsonData = false;
        }
        return $authJsonData;
    }

    /**
     * Gets auth.json
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function getAuthJson()
    {
        $directory = $this->getFilesystem()->getDirectoryRead(DirectoryList::COMPOSER_HOME);
        if ($directory->isExist($this->pathToAuthFile) && $directory->isReadable($this->pathToAuthFile)) {
            try {
                $data = $directory->readFile($this->pathToAuthFile);
                return json_decode($data, true);
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
            }
        }
        return false;
    }

    /**
     * Removes credentials from auth.json
     *
     * @return bool
     * @throws \Exception
     */
    public function removeCredentials()
    {
        $serviceUrl = $this->getCredentialBaseUrl();
        $directory = $this->getFilesystem()->getDirectoryRead(DirectoryList::COMPOSER_HOME);
        if ($directory->isExist($this->pathToAuthFile) && $directory->isReadable($this->pathToAuthFile)) {
            try {
                $authJsonData = $this->getAuthJson();
                if (!empty($authJsonData) && isset($authJsonData['http-basic'][$serviceUrl])) {
                    unset($authJsonData['http-basic'][$serviceUrl]);
                    if (empty($authJsonData['http-basic'])) {
                        return unlink(getenv('COMPOSER_HOME') . DIRECTORY_SEPARATOR . $this->pathToAuthFile);
                    } else {
                        $data = json_encode($authJsonData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
                        $this->getDirectory()->writeFile(
                            DirectoryList::COMPOSER_HOME . DIRECTORY_SEPARATOR . $this->pathToAuthFile,
                            $data
                        );
                        return true;
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }

    /**
     * Saves auth.json file
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function saveAuthJson($username, $password)
    {
        $commandParams = [
            self::PARAM_COMMAND =>  self::PARAM_CONFIG,
            self::PARAM_KEY => self::PARAM_HTTPBASIC . $this->getCredentialBaseUrl(),
            self::PARAM_VALUE =>  [$username, $password],
            self::PARAM_GLOBAL => true
        ];
        try {
            $this->getApplication()->runComposerCommand($commandParams);
            return true;
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Saves composer packages available for install to cache
     *
     * @param array $availablePackages
     * @return bool|string
     */
    public function savePackagesForInstallToCache($availablePackages)
    {
        $syncInfo = [];
        $syncInfo['packages'] = $availablePackages;
        $data = json_encode($syncInfo, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        try {
            $this->getDirectory()->writeFile($this->pathToInstallPackagesCacheFile, $data);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            return false;
        }
        return $data;
    }

    /**
     * Sync and cache list of available for install versions for packages
     *
     * @return bool|string
     */
    public function getPackagesForInstall()
    {
        $actualInstallackages = [];
        $installPackagesInfo = $this->loadPackagesForInstallFromCache();
        if (!$installPackagesInfo) {
            return false;
        }

        try {
            $installPackages = $installPackagesInfo['packages'];
            $availablePackageNames = array_column(
                $this->getComposerInformation()->getInstalledMagentoPackages(),
                'name'
            );
            foreach ($installPackages as $package) {
                if (!in_array($package['name'], $availablePackageNames) &&
                    in_array($package['type'], $this->getComposerInformation()->getPackagesTypes())
                ) {
                    $actualInstallackages[$package['name']] = $package;
                }
            }
            $installPackagesInfo['packages'] = $actualInstallackages;
            return $installPackagesInfo;
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * Load composer packages available for install from cache
     *
     * @return bool|string
     */
    public function loadPackagesForInstallFromCache()
    {
        if ($this->getDirectory()->isExist($this->pathToInstallPackagesCacheFile)
            && $this->getDirectory()->isReadable($this->pathToInstallPackagesCacheFile)) {
            try {
                $data = $this->getDirectory()->readFile($this->pathToInstallPackagesCacheFile);
                return json_decode($data, true);
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
            }
        }
        return false;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getComposerInformation()
    {
        return $this->composerInformation;
    }

    /**
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return \Magento\Framework\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return Filesystem\Directory\Write|Filesystem\Directory\WriteInterface
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return \Magento\Composer\MagentoComposerApplication
     */
    public function getApplication()
    {
        return $this->application;
    }
}
