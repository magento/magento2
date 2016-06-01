<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\View\Model\JsonModel;

/**
 * Class PackagesAuth, checks, saves and removes auth details related to packages.
 */
class PackagesAuth
{
    /**#@+
     * Composer auth.json keys
     */
    const KEY_HTTPBASIC = 'http-basic';
    const KEY_USERNAME = 'username';
    const KEY_PASSWORD = 'password';
    /**#@-*/

    /**#@+
     * Filenames for auth and package info
     */
    const PATH_TO_AUTH_FILE = 'auth.json';
    const PATH_TO_PACKAGES_FILE = 'packages.json';
    /**#@-*/

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

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
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->curlClient = $curl;
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    private function getPackagesJsonUrl()
    {
        return $this->urlPrefix .  $this->getCredentialBaseUrl() .  '/packages.json';
    }

    /**
     * @return string
     */
    public function getCredentialBaseUrl()
    {
        $config = $this->serviceLocator->get('config');
        return $config['marketplace']['check_credentials_url'];
    }

    /**
     * @param string $token
     * @param string $secretKey
     * @return string
     */
    public function checkCredentials($token, $secretKey)
    {
        $serviceUrl = $this->getPackagesJsonUrl();
        $this->curlClient->setCredentials($token, $secretKey);
        try {
            $this->curlClient->post($serviceUrl, []);
            if ($this->curlClient->getStatus() == 200) {
                $packagesInfo = $this->curlClient->getBody();
                $directory = $this->filesystem->getDirectoryWrite(DirectoryList::COMPOSER_HOME);
                $directory->writeFile(self::PATH_TO_PACKAGES_FILE, $packagesInfo);
                return \Zend_Json::encode(['success' => true]);
            } else {
                return \Zend_Json::encode(['success' => false, 'message' => 'Bad credentials']);
            }
        } catch (\Exception $e) {
            return \Zend_Json::encode(['success' => false, 'message' => $e->getMessage()]);
        }
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
    private function getAuthJson()
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::COMPOSER_HOME);
        if ($directory->isExist(self::PATH_TO_AUTH_FILE) && $directory->isReadable(self::PATH_TO_AUTH_FILE)) {
            try {
                $data = $directory->readFile(self::PATH_TO_AUTH_FILE);
                return json_decode($data, true);
            } catch (\Exception $e) {
                throw new \Exception('Error in reading Auth file');
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
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::COMPOSER_HOME);
        if ($directory->isExist(self::PATH_TO_AUTH_FILE) && $directory->isReadable(self::PATH_TO_AUTH_FILE)) {
            $authJsonData = $this->getAuthJson();
            if (isset($authJsonData['http-basic']) && isset($authJsonData['http-basic'][$serviceUrl])) {
                unset($authJsonData['http-basic'][$serviceUrl]);
                if ($authJsonData === ['http-basic' => []]) {
                    return $directory->delete(self::PATH_TO_AUTH_FILE);
                } else {
                    $data = json_encode($authJsonData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
                    return $data !== false && $directory->writeFile(self::PATH_TO_AUTH_FILE, $data);
                }
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
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::COMPOSER_HOME);
        $authContent = [
            PackagesAuth::KEY_HTTPBASIC => [
                $this->getCredentialBaseUrl() => [
                    PackagesAuth::KEY_USERNAME => "$username",
                    PackagesAuth::KEY_PASSWORD => "$password"
                ]
            ]
        ];
        $json = new \Zend\View\Model\JsonModel($authContent);
        $json->setOption('prettyPrint', true);
        $jsonContent = $json->serialize();

        return $directory->writeFile(self::PATH_TO_AUTH_FILE, $jsonContent);
    }
}
