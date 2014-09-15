<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\Installer;

/**
 * Config installer
 */
class Config
{
    const TMP_INSTALL_DATE_VALUE = 'd-d-d-d-d';

    const TMP_ENCRYPT_KEY_VALUE = 'k-k-k-k-k';

    /**
     * Path to local configuration file
     *
     * @var string
     */
    protected $_localConfigFile = 'local.xml';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_pubDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_configDirectory;

    /**
     * Store Manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_pubDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::PUB_DIR);
        $this->_configDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::CONFIG_DIR);
        $this->messageManager = $messageManager;
    }

    /**
     * Generate installation data and record them into local.xml using local.xml.template
     *
     * @param array $config
     * @return array
     */
    public function install($config)
    {
        $defaults = array(
            'root_dir' => $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::ROOT_DIR),
            'app_dir' => $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::APP_DIR),
            'var_dir' => $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR),
            'base_url' => $this->_request->getDistroBaseUrl()
        );
        foreach ($defaults as $index => $value) {
            if (!isset($config[$index])) {
                $config[$index] = $value;
            }
        }

        if (isset($config['unsecure_base_url'])) {
            $config['unsecure_base_url'] .= substr($config['unsecure_base_url'], -1) != '/' ? '/' : '';
            if (strpos($config['unsecure_base_url'], 'http') !== 0) {
                $config['unsecure_base_url'] = 'http://' . $config['unsecure_base_url'];
            }
            if (empty($config['skip_base_url_validation'])) {
                $this->_checkUrl($config['unsecure_base_url']);
            }
        }
        if (isset($config['secure_base_url'])) {
            $config['secure_base_url'] .= substr($config['secure_base_url'], -1) != '/' ? '/' : '';
            if (strpos($config['secure_base_url'], 'http') !== 0) {
                $config['secure_base_url'] = 'https://' . $config['secure_base_url'];
            }

            if (!empty($config['use_secure']) && empty($config['skip_url_validation'])) {
                $this->_checkUrl($config['secure_base_url']);
            }
        }

        $config['date'] = self::TMP_INSTALL_DATE_VALUE;
        $config['key'] = self::TMP_ENCRYPT_KEY_VALUE;
        $config['var_dir'] = $config['root_dir'] . '/var';

        $config['use_script_name'] = isset($config['use_script_name']) ? 'true' : 'false';

        $contents = $this->_configDirectory->readFile('local.xml.template');
        foreach ($config as $index => $value) {
            $contents = str_replace('{{' . $index . '}}', '<![CDATA[' . $value . ']]>', $contents);
        }

        $this->_configDirectory->writeFile($this->_localConfigFile, $contents);
        $this->_configDirectory->changePermissions($this->_localConfigFile, 0777);

        return $config;
    }

    /**
     * @return \Magento\Framework\Object
     */
    public function getFormData()
    {
        $uri = \Zend_Uri::factory($this->_storeManager->getStore()->getBaseUrl('web'));

        $baseUrl = $uri->getUri();
        if ($uri->getScheme() !== 'https') {
            $uri->setPort(null);
            $baseSecureUrl = str_replace('http://', 'https://', $uri->getUri());
        } else {
            $baseSecureUrl = $uri->getUri();
        }

        $data = new \Magento\Framework\Object();
        $data->setDbHost(
            'localhost'
        )->setDbName(
            'magento'
        )->setDbUser(
            ''
        )->setDbModel(
            'mysql4'
        )->setDbPass(
            ''
        )->setSecureBaseUrl(
            $baseSecureUrl
        )->setUnsecureBaseUrl(
            $baseUrl
        )->setBackendFrontname(
            'backend'
        )->setEnableCharts(
            '1'
        );
        return $data;
    }

    /**
     * Check validity of a base URL
     *
     * @param string $baseUrl
     * @return void
     * @throws \Magento\Framework\Model\Exception
     * @throws \Exception
     */
    protected function _checkUrl($baseUrl)
    {
        try {
            $staticFile = $this->_findFirstFileRelativePath('', '/.+\.(html?|js|css|gif|jpe?g|png)$/');
            $staticUrl = $baseUrl . $this->_filesystem->getUri(
                \Magento\Framework\App\Filesystem::PUB_DIR
            ) . '/' . $staticFile;
            $client = new \Magento\Framework\HTTP\ZendClient($staticUrl);
            $response = $client->request('GET');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('The URL "%1" is not accessible.', $baseUrl));
            throw $e;
        }
        if ($response->getStatus() != 200) {
            $this->messageManager->addError(__('The URL "%1" is invalid.', $baseUrl));
            throw new \Magento\Framework\Model\Exception(__('Response from the server is invalid.'));
        }
    }

    /**
     * Find a relative path to a first file located in a directory or its descendants
     *
     * @param string $dir Directory to search for a file within
     * @param string $pattern PCRE pattern a file name has to match
     * @return string|null
     */
    protected function _findFirstFileRelativePath($dir, $pattern = '/.*/')
    {
        $childDirs = array();
        foreach ($this->_pubDirectory->read($dir) as $itemPath) {
            if ($this->_pubDirectory->isFile($itemPath)) {
                if (preg_match($pattern, $itemPath)) {
                    return $itemPath;
                }
            } else {
                $childDirs[$itemPath] = $itemPath;
            }
        }
        foreach ($childDirs as $dirName => $dirPath) {
            $filePath = $this->_findFirstFileRelativePath($dirPath, $pattern);
            if ($filePath) {
                return $filePath;
            }
        }
        return null;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function replaceTmpInstallDate($date = 'now')
    {
        $stamp = strtotime((string)$date);
        $localXml = $this->_configDirectory->readFile($this->_localConfigFile);
        $localXml = str_replace(self::TMP_INSTALL_DATE_VALUE, date('r', $stamp), $localXml);
        $this->_configDirectory->writeFile($this->_localConfigFile, $localXml);

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function replaceTmpEncryptKey($key)
    {
        $localXml = $this->_configDirectory->readFile($this->_localConfigFile);
        $localXml = str_replace(self::TMP_ENCRYPT_KEY_VALUE, $key, $localXml);
        $this->_configDirectory->writeFile($this->_localConfigFile, $localXml);

        return $this;
    }
}
