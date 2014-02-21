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
 * @category    Magento
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Model is responsible for replacing default vcl template
 * file configuration with user-defined from configuration
 *
 * @category   Magento
 * @package    Magento_PageCache
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\PageCache\Model;

use Magento\App\Filesystem;

/**
 * Class Config
 *
 * @package Magento\PageCache\Model
 */
class Config
{
    /**#@+
     * XML path to Varnish settings
     */
    const XML_PAGECACHE_TTL = 'system/full_page_cache/ttl';
    const XML_VARNISH_PAGECACHE_ACCESS_LIST = 'system/full_page_cache/varnish/access_list';
    const XML_VARNISH_PAGECACHE_BACKEND_PORT = 'system/full_page_cache/varnish/backend_port';
    const XML_VARNISH_PAGECACHE_BACKEND_HOST = 'system/full_page_cache/varnish/backend_host';
    const XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX = 'design/theme/ua_regexp';
    /**#@-*/

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * XML path to value for saving temporary .vcl configuration
     */
    const VARNISH_CONFIGURATION_PATH = 'system/full_page_cache/varnish/path';

    /**
     * @var \Magento\Filesystem\Directory\WriteInterface
     */
    protected $_modulesDirectory;

    public function __construct(
        \Magento\App\Filesystem $filesystem,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\ConfigInterface $config
    ) {
        $this->_modulesDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::MODULES_DIR);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_config = $config;
    }

    /**
     * Return generated varnish.vcl configuration file
     *
     * @return string
     */
    public function getVclFile()
    {
        $data = $this->_modulesDirectory->readFile(
            $this->_config->getValue(self::VARNISH_CONFIGURATION_PATH)
        );
        return strtr($data, $this->_getReplacements());
    }

    /**
     * Prepare data for VCL config
     *
     * @return array
     */
    protected function _getReplacements()
    {
        return array(
            '{{ host }}' => $this->_coreStoreConfig->getConfig(self::XML_VARNISH_PAGECACHE_BACKEND_HOST),
            '{{ port }}' => $this->_coreStoreConfig->getConfig(self::XML_VARNISH_PAGECACHE_BACKEND_PORT),
            '{{ ips }}' => $this->_getAccessList(),
            '{{ design_exceptions_code }}' => $this->_getDesignExceptions()
        );
    }

    /**
     * Get IPs access list that can purge Varnish configuration for config file generation
     * and transform it to appropriate view
     *
     * acl purge{
     *  "127.0.0.1";
     *  "127.0.0.2";
     *
     * @return mixed|null|string
     */
    protected function _getAccessList()
    {
        $result = '';
        $tpl = "    \"%s\";";
        $accessList = $this->_coreStoreConfig->getConfig(self::XML_VARNISH_PAGECACHE_ACCESS_LIST);
        if (!empty($accessList)) {
            $ips = explode(', ', $accessList);
            foreach ($ips as $ip) {
                $result[] = sprintf($tpl, $ip);
            }
            return implode("\n", $result);
        }
        return $result;
    }

    /**
     * Get regexs for design exceptions
     * Different browser user-agents may use different themes
     * Varnish supports regex with internal modifiers only so
     * we have to convert "/pattern/iU" into "(?Ui)pattern"
     *
     * @return string
     */
    protected function _getDesignExceptions()
    {
        $result = '';
        $tpl = "%s (req.http.user-agent ~ \"%s\") {\n"
             . "        hash_data(\"%s\");\n"
             . "    }";

        $expressions = $this->_coreStoreConfig->getConfig(self::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX);
        if ($expressions) {
            $rules = array_values(unserialize($expressions));
            foreach ($rules as $i => $rule) {
                if (preg_match('/^[\W]{1}(.*)[\W]{1}(\w+)?$/', $rule['regexp'], $matches)) {
                    if (!empty($matches[2])) {
                        $pattern = sprintf("(?%s)%s", $matches[2], $matches[1]);
                    } else {
                        $pattern = $matches[1];
                    }
                    $if = ($i == 0) ? 'if' : ' elsif';
                    $result .= sprintf($tpl, $if, $pattern, $rule['value']);
                }
            }
        }
        return $result;
    }
}
