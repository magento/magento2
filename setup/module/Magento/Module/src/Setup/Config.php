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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Module\Setup;

use Magento\Filesystem\Directory\Write;
use Magento\Filesystem\Filesystem;

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
    protected $localConfigFile = 'local.xml';

    /**
     * @var array
     */
    protected $configData = array();

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Write
     */
    protected $configDirectory;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        $this->configDirectory = $filesystem->getDirectoryWrite('etc');
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setConfigData($data)
    {
        if (is_array($data)) {
            $this->configData = $this->convert($data);
        }
        return $this;
    }


    /**
     * Retrieve config data
     *
     * @return array
     */
    public function getConfigData()
    {
        return $this->configData;
    }

    /**
     * Generate installation data and record them into local.xml using local.xml.template
     *
     * @return void
     */
    public function install()
    {
        $this->configData['date'] = self::TMP_INSTALL_DATE_VALUE;
        $this->configData['key'] = self::TMP_ENCRYPT_KEY_VALUE;

        $this->checkData();

        $contents = $this->configDirectory->readFile('local.xml.template');
        foreach ($this->configData as $index => $value) {
            $contents = str_replace('{{' . $index . '}}', '<![CDATA[' . $value . ']]>', $contents);
        }

        $this->configDirectory->writeFile($this->localConfigFile, $contents, LOCK_EX);
        $this->configDirectory->changePermissions($this->localConfigFile, 0777);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function replaceTmpInstallDate($date = 'now')
    {
        $stamp = strtotime((string)$date);
        $localXml = $this->configDirectory->readFile($this->localConfigFile);
        $localXml = str_replace(self::TMP_INSTALL_DATE_VALUE, date('r', $stamp), $localXml);
        $this->configDirectory->writeFile($this->localConfigFile, $localXml, LOCK_EX);

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function replaceTmpEncryptKey($key)
    {
        $localXml = $this->configDirectory->readFile($this->localConfigFile);
        $localXml = str_replace(self::TMP_ENCRYPT_KEY_VALUE, $key, $localXml);
        $this->configDirectory->writeFile($this->localConfigFile, $localXml, LOCK_EX);

        return $this;
    }

    /**
     * Convert config
     * @param array $source
     * @return array
     */
    protected function convert(array $source = array())
    {
        $result = array();
        $result['db_host'] = isset($source['db']['host']) ? $source['db']['host'] : '';
        $result['db_name'] = isset($source['db']['name']) ? $source['db']['name'] : '';
        $result['db_user'] = isset($source['db']['user']) ? $source['db']['user'] :'';
        $result['db_pass'] = isset($source['db']['password']) ? $source['db']['password'] : '';
        $result['db_prefix'] = isset($source['db']['tablePrefix']) ? $source['db']['tablePrefix'] : '';
        $result['session_save'] = 'files';
        $result['backend_frontname'] = isset($source['config']['address']['admin'])
            ? $source['config']['address']['admin']
            : '';
        $result['db_model'] = '';
        $result['db_init_statements'] = '';

        $result['admin_username'] = isset($source['admin']['username']) ? $source['admin']['username'] : '';
        $result['admin_password'] = isset($source['admin']['password']) ? $source['admin']['password'] : '';
        $result['admin_email'] = isset($source['admin']['email']) ? $source['admin']['email'] : '';

        return $result;
    }

    /**
     * Check database connection data
     *
     * @throws \Exception
     */
    protected function checkData()
    {
        if (!isset($this->configData['db_name']) || empty($this->configData['db_name'])) {
            throw new \Exception('The Database Name field cannot be empty.');
        }
        //make all table prefix to lower letter
        if ($this->configData['db_prefix'] != '') {
            $this->configData['db_prefix'] = strtolower($this->configData['db_prefix']);
        }
        //check table prefix
        if ($this->configData['db_prefix'] != '') {
            if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $this->configData['db_prefix'])) {
                throw new \Exception(
                    'The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_); the first character should be a letter.'
                );
            }
        }
    }
}
