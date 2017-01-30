<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

/**
 * Model is responsible for replacing default vcl template
 * file configuration with user-defined from configuration
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

/**
 * Class Config
 *
 */
class Config
{
    /**
     * Cache types
     */
    const BUILT_IN = 1;

    const VARNISH = 2;

    /**
     * XML path to Varnish settings
     */
    const XML_PAGECACHE_TTL = 'system/full_page_cache/ttl';

    const XML_PAGECACHE_TYPE = 'system/full_page_cache/caching_application';

    const XML_VARNISH_PAGECACHE_ACCESS_LIST = 'system/full_page_cache/varnish/access_list';

    const XML_VARNISH_PAGECACHE_BACKEND_PORT = 'system/full_page_cache/varnish/backend_port';

    const XML_VARNISH_PAGECACHE_BACKEND_HOST = 'system/full_page_cache/varnish/backend_host';

    const XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX = 'design/theme/ua_regexp';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * XML path to Varnish 3 config template path
     */
    const VARNISH_3_CONFIGURATION_PATH = 'system/full_page_cache/varnish3/path';

    /**
     * XML path to Varnish 4 config template path
     */
    const VARNISH_4_CONFIGURATION_PATH = 'system/full_page_cache/varnish4/path';

    /**
     * @var \Magento\Framework\App\Cache\StateInterface $_cacheState
     */
    protected $_cacheState;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param Dir\Reader $reader
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->readFactory = $readFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_cacheState = $cacheState;
        $this->reader = $reader;
    }

    /**
     * Return currently selected cache type: built in or varnish
     *
     * @return int
     * @api
     */
    public function getType()
    {
        return $this->_scopeConfig->getValue(self::XML_PAGECACHE_TYPE);
    }

    /**
     * Return page lifetime
     *
     * @return int
     * @api
     */
    public function getTtl()
    {
        return $this->_scopeConfig->getValue(self::XML_PAGECACHE_TTL);
    }

    /**
     * Return generated varnish.vcl configuration file
     *
     * @param string $vclTemplatePath
     * @return string
     * @api
     */
    public function getVclFile($vclTemplatePath)
    {
        $moduleEtcPath = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_PageCache');
        $configFilePath = $moduleEtcPath . '/' . $this->_scopeConfig->getValue($vclTemplatePath);
        $directoryRead = $this->readFactory->create($moduleEtcPath);
        $configFilePath = $directoryRead->getRelativePath($configFilePath);
        $data = $directoryRead->readFile($configFilePath);
        return strtr($data, $this->_getReplacements());
    }

    /**
     * Prepare data for VCL config
     *
     * @return array
     */
    protected function _getReplacements()
    {
        return [
            '/* {{ host }} */' => $this->_scopeConfig->getValue(
                self::XML_VARNISH_PAGECACHE_BACKEND_HOST,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            '/* {{ port }} */' => $this->_scopeConfig->getValue(
                self::XML_VARNISH_PAGECACHE_BACKEND_PORT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            '/* {{ ips }} */' => $this->_getAccessList(),
            '/* {{ design_exceptions_code }} */' => $this->_getDesignExceptions(),
            // http headers get transformed by php `X-Forwarded-Proto: https` becomes $SERVER['HTTP_X_FORWARDED_PROTO'] = 'https'
            // Apache and Nginx drop all headers with underlines by default.
            '/* {{ ssl_offloaded_header }} */' => str_replace('_', '-', $this->_scopeConfig->getValue(
                \Magento\Framework\HTTP\PhpEnvironment\Request::XML_PATH_OFFLOADER_HEADER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE))

        ];
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
        $accessList = $this->_scopeConfig->getValue(
            self::XML_VARNISH_PAGECACHE_ACCESS_LIST,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!empty($accessList)) {
            $ips = explode(',', $accessList);
            foreach ($ips as $ip) {
                $result[] = sprintf($tpl, trim($ip));
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
        $tpl = "%s (req.http.user-agent ~ \"%s\") {\n" . "        hash_data(\"%s\");\n" . "    }";

        $expressions = $this->_scopeConfig->getValue(
            self::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($expressions) {
            $rules = array_values(unserialize($expressions));
            foreach ($rules as $i => $rule) {
                if (preg_match('/^[\W]{1}(.*)[\W]{1}(\w+)?$/', $rule['regexp'], $matches)) {
                    if (!empty($matches[2])) {
                        $pattern = sprintf("(?%s)%s", $matches[2], $matches[1]);
                    } else {
                        $pattern = $matches[1];
                    }
                    $if = $i == 0 ? 'if' : ' elsif';
                    $result .= sprintf($tpl, $if, $pattern, $rule['value']);
                }
            }
        }
        return $result;
    }

    /**
     * Whether a cache type is enabled in Cache Management Grid
     *
     * @return bool
     * @api
     */
    public function isEnabled()
    {
        return $this->_cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }
}
