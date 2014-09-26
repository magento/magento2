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

namespace Magento\Setup\Module\Setup;

use Magento\Filesystem\Directory\Write;
use Magento\Filesystem\Filesystem;

/**
 * Deployment configuration model
 */
class Config
{
    /**#@+
     * Possible variables of the deployment configuration
     */
    const KEY_DATE    = 'date';
    const KEY_DB_HOST = 'db_host';
    const KEY_DB_NAME = 'db_name';
    const KEY_DB_USER = 'db_user';
    const KEY_DB_PASS = 'db_pass';
    const KEY_DB_PREFIX = 'db_prefix';
    const KEY_DB_MODEL = 'db_model';
    const KEY_DB_INIT_STATEMENTS = 'db_init_statements';
    const KEY_SESSION_SAVE = 'session_save';
    const KEY_BACKEND_FRONTNAME = 'backend_frontname';
    const KEY_ENCRYPTION_KEY = 'key';
    /**#@- */

    /**#@+
     * Paths to deployment config file and template
     */
    const DEPLOYMENT_CONFIG_FILE = 'local.xml';
    const DEPLOYMENT_CONFIG_FILE_TEMPLATE = 'local.xml.template';
    /**#@- */

    /**
     * The data values + default values
     *
     * @var string[]
     */
    private $data = [
        self::KEY_DATE => '',
        self::KEY_DB_HOST => '',
        self::KEY_DB_NAME => '',
        self::KEY_DB_USER => '',
        self::KEY_DB_PASS => '',
        self::KEY_DB_PREFIX => '',
        self::KEY_DB_MODEL => 'mysql4',
        self::KEY_DB_INIT_STATEMENTS => 'SET NAMES utf8;',
        self::KEY_SESSION_SAVE => 'files',
        self::KEY_BACKEND_FRONTNAME => 'backend',
        self::KEY_ENCRYPTION_KEY => '',
    ];

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Config Directory
     *
     * @var Write
     */
    protected $configDirectory;

    /**
     * Default Constructor
     *
     * @param Filesystem $filesystem
     * @param string[] $data
     */
    public function __construct(
        Filesystem $filesystem,
        $data = []
    ) {
        $this->filesystem = $filesystem;
        $this->configDirectory = $filesystem->getDirectoryWrite('etc');

        if ($data) {
            $this->update($data);
        }
    }

    /**
     * Retrieve config data
     *
     * @return string[]
     */
    public function getConfigData()
    {
        return $this->data;
    }

    /**
     * Get a value from config data by key
     *
     * @param string $key
     * @return null|string
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Update data
     *
     * @param string[] $data
     * @return void
     */
    public function update($data)
    {
        $new = [];
        foreach (array_keys($this->data) as $key) {
            $new[$key] = isset($data[$key]) ? $data[$key] : $this->data[$key];
        }
        $this->checkData($new);
        $this->data = $new;
    }

    /**
     * Loads configuration the deployment configuration file
     *
     * @return void
     */
    public function loadFromFile()
    {
        $xmlData = $this->configDirectory->readFile(self::DEPLOYMENT_CONFIG_FILE);
        $xmlObj = @simplexml_load_string($xmlData, NULL, LIBXML_NOCDATA);
        $xmlConfig = json_decode(json_encode((array)$xmlObj), true);
        $data = $this->convertFromConfigData((array)$xmlConfig);
        $this->update($data);
    }

    /**
     * Exports data to a deployment configuration file
     *
     * @return void
     * @throws \Exception
     */
    public function saveToFile()
    {
        $contents = $this->configDirectory->readFile(self::DEPLOYMENT_CONFIG_FILE_TEMPLATE);
        foreach ($this->data as $index => $value) {
            $contents = str_replace('{{' . $index . '}}', '<![CDATA[' . $value . ']]>', $contents);
        }
        if (preg_match('(\{\{.+?\}\})', $contents, $matches)) {
            throw new \Exception("Some of the keys have not been replaced in the template: {$matches[1]}");
        }

        $this->configDirectory->writeFile(self::DEPLOYMENT_CONFIG_FILE, $contents, LOCK_EX);
        $this->configDirectory->changePermissions(self::DEPLOYMENT_CONFIG_FILE, 0777);
    }

    /**
     * Convert config
     *
     * @param array $source
     * @return array
     */
    private function convertFromConfigData(array $source)
    {
        $result = array();
        if (isset($source['connection']['host']) && !is_array($source['connection']['host'])) {
            $result[self::KEY_DB_HOST] = $source['connection']['host'];
        }
        if (isset($source['connection']['dbName']) && !is_array($source['connection']['dbName'])) {
            $result[self::KEY_DB_NAME] = $source['connection']['dbName'];
        }
        if (isset($source['connection']['username']) && !is_array($source['connection']['username'])) {
            $result[self::KEY_DB_USER] = $source['connection']['username'];
        }
        if (isset($source['connection']['password']) && !is_array($source['connection']['password'])) {
            $result[self::KEY_DB_PASS] = $source['connection']['password'];
        }
        if (isset($source['db']['table_prefix']) && !is_array($source['db']['table_prefix'])) {
            $result[self::KEY_DB_PREFIX] = $source['db']['table_prefix'];
        }
        if (isset($source['session_save']) && !is_array($source['session_save'])) {
            $result[self::KEY_SESSION_SAVE] = $source['session_save'];
        }
        if (isset($source['config']['address']['admin']) && !is_array($source['config']['address']['admin'])) {
            $result[self::KEY_BACKEND_FRONTNAME] = $source['config']['address']['admin'];
        }
        if (isset($source['connection']['initStatements']) && !is_array($source['connection']['initStatements']) ) {
            $result[self::KEY_DB_INIT_STATEMENTS] = $source['connection']['initStatements'];
        }
        if (isset($source['crypt']['key'])) {
            $result[self::KEY_ENCRYPTION_KEY] = $source['crypt']['key'];
        }
        if (isset($source['install']['date'])) {
            $result[self::KEY_DATE] = $source['install']['date'];
        }
        return $result;
    }

    /**
     * Check database connection data
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    private function checkData(array $data)
    {
        if (empty($data[self::KEY_ENCRYPTION_KEY])) {
            throw new \Exception('Encryption key must not be empty.');
        }
        if (empty($data[self::KEY_DATE])) {
            throw new \Exception('Installation date must not be empty.');
        }
        if (empty($data[self::KEY_DB_NAME])) {
            throw new \Exception('The Database Name field cannot be empty.');
        }
        $prefix = $data[self::KEY_DB_PREFIX];
        if ($prefix != '') {
            $prefix = strtolower($prefix);
            if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $prefix)) {
                throw new \Exception(
                    'The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_); '
                    . 'the first character should be a letter.'
                );
            }
        }
    }
}
