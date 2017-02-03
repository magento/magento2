<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Zend\View\Model\JsonModel;

class MarketplaceManager
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
     * Composer auth.json keys
     */
    const KEY_HTTPBASIC = 'http-basic';
    const KEY_USERNAME = 'username';
    const KEY_PASSWORD = 'password';

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directory;

    /**
     * @var string
     */
    protected $pathToInstallPackagesCacheFile = 'install_composer_packages.json';

    /**
     * @var array
     */
    protected $errorCodes = [401, 403, 404];

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator,
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->composerInformation = $composerInformation;
        $this->curlClient = $curl;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
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
        return $config['marketplace']['check_credentials_url'];
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
        $serviceUrl = $this->getPackagesJsonUrl();
        $this->getCurlClient()->setCredentials($token, $secretKey);
        try {
            $this->getCurlClient()->post($serviceUrl, []);
            if ($this->getCurlClient()->getStatus() == 200) {
                return \Zend_Json::encode(['success' => true]);
            } else {
                return \Zend_Json::encode(['success' => false, 'message' => 'Bad credentials']);
            }
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
            foreach ($packagesJsonData['packages'] as $packageName => $package) {
                if (!empty($package) && is_array($package)) {
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
            return $this->savePackagesForInstallToCache($installPackages) ? true : false;
        } catch (\Exception $e) {
        }
        return false;
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
            in_array($package['type'], $this->getComposerInformation()->getPackagesTypes()) &&
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
        $authContent = [
            self::KEY_HTTPBASIC => [
                $this->getCredentialBaseUrl() => [
                    self::KEY_USERNAME => "$username",
                    self::KEY_PASSWORD => "$password"
                ]
            ]
        ];
        $json = new JsonModel($authContent);
        $json->setOption('prettyPrint', true);
        $jsonContent = $json->serialize();

        return $this->getDirectory()->writeFile(
            DirectoryList::COMPOSER_HOME . DIRECTORY_SEPARATOR . $this->pathToAuthFile,
            $jsonContent
        );
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
            $metaPackageByPackage = $this->getMetaPackageForPackage($installPackages);
            foreach ($installPackages as $package) {
                if (!in_array($package['name'], $availablePackageNames) &&
                    in_array($package['type'], $this->getComposerInformation()->getPackagesTypes()) &&
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
        }
        return false;
    }

    /**
     * 
     * @param array $packages
     * @return array
     */
    protected function getMetaPackageForPackage($packages)
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
}
