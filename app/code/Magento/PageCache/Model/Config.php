<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Module\Dir;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Varnish\VclGeneratorFactory;

/**
 * Model is responsible for replacing default vcl template file configuration with user-defined from configuration
 *
 * @api
 * @since 100.0.2
 */
class Config
{
    /**
     * Cache types
     */
    public const BUILT_IN = 1;

    public const VARNISH = 2;

    /**
     * XML path to Varnish settings
     */
    public const XML_PAGECACHE_TTL = 'system/full_page_cache/ttl';

    public const XML_PAGECACHE_TYPE = 'system/full_page_cache/caching_application';

    public const XML_VARNISH_PAGECACHE_ACCESS_LIST = 'system/full_page_cache/varnish/access_list';

    public const XML_VARNISH_PAGECACHE_BACKEND_PORT = 'system/full_page_cache/varnish/backend_port';

    public const XML_VARNISH_PAGECACHE_BACKEND_HOST = 'system/full_page_cache/varnish/backend_host';

    public const XML_VARNISH_PAGECACHE_GRACE_PERIOD = 'system/full_page_cache/varnish/grace_period';

    public const XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX = 'design/theme/ua_regexp';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * XML path to Varnish 6 config template path
     */
    public const VARNISH_6_CONFIGURATION_PATH = 'system/full_page_cache/varnish6/path';

    /**
     * @deprecated Varnish 5 is EOL
     * @see VARNISH_6_CONFIGURATION_PATH
     * XML path to Varnish 5 config template path
     */
    public const VARNISH_5_CONFIGURATION_PATH = 'system/full_page_cache/varnish5/path';

    /**
     * @deprecated Varnish 4 is EOL
     * @see VARNISH_6_CONFIGURATION_PATH
     * XML path to Varnish 4 config template path
     */
    public const VARNISH_4_CONFIGURATION_PATH = 'system/full_page_cache/varnish4/path';

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
     * @var Json
     */
    private $serializer;

    /**
     * @var VclGeneratorFactory
     */
    private $vclGeneratorFactory;

    /**
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param Dir\Reader $reader
     * @param VclGeneratorFactory $vclGeneratorFactory
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Module\Dir\Reader $reader,
        VclGeneratorFactory $vclGeneratorFactory,
        Json $serializer = null
    ) {
        $this->readFactory = $readFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_cacheState = $cacheState;
        $this->reader = $reader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->vclGeneratorFactory = $vclGeneratorFactory;
    }

    /**
     * Return currently selected cache type: built in or varnish
     *
     * @return int
     */
    public function getType()
    {
        return (int)$this->_scopeConfig->getValue(self::XML_PAGECACHE_TYPE);
    }

    /**
     * Return page lifetime
     *
     * @return int
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
     * @deprecated 100.2.0
     * @see \Magento\PageCache\Model\VclGeneratorInterface::generateVcl
     */
    public function getVclFile($vclTemplatePath)
    {
        $accessList = $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_ACCESS_LIST);
        $designExceptions = $this->_scopeConfig->getValue(
            self::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        switch ($vclTemplatePath) {
            case self::VARNISH_6_CONFIGURATION_PATH:
                $version = 6;
                break;
            default:
                $version = 6;
        }
        $sslOffloadedHeader = $this->_scopeConfig->getValue(
            Request::XML_PATH_OFFLOADER_HEADER
        );
        $vclGenerator = $this->vclGeneratorFactory->create(
            [
                'backendHost' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_BACKEND_HOST),
                'backendPort' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_BACKEND_PORT),
                'accessList' => $accessList ? explode(',', $accessList) : [],
                'designExceptions' => $designExceptions ? $this->serializer->unserialize($designExceptions) : [],
                'sslOffloadedHeader' => $sslOffloadedHeader,
                'gracePeriod' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_GRACE_PERIOD)
            ]
        );
        return $vclGenerator->generateVcl($version);
    }

    /**
     * Prepare data for VCL config
     *
     * @return array
     * @deprecated 100.2.0
     * @see \Magento\PageCache\Model\VclGeneratorInterface::generateVcl
     */
    protected function _getReplacements()
    {
        return [
            '/* {{ host }} */' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_BACKEND_HOST),
            '/* {{ port }} */' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_BACKEND_PORT),
            '/* {{ ips }} */' => $this->_getAccessList(),
            '/* {{ design_exceptions_code }} */' => $this->_getDesignExceptions(),
            // http headers get transformed by php `X-Forwarded-Proto: https`
            // becomes $SERVER['HTTP_X_FORWARDED_PROTO'] = 'https'
            // Apache and Nginx drop all headers with underlines by default.
            '/* {{ ssl_offloaded_header }} */' => str_replace(
                '_',
                '-',
                $this->_scopeConfig->getValue(Request::XML_PATH_OFFLOADER_HEADER) ?? ''
            ),
            '/* {{ grace_period }} */' => $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_GRACE_PERIOD)
        ];
    }

    /**
     * Get IPs access list allowed purge Varnish config for config file generation and transform it to appropriate view
     *
     * Example acl_purge{
     *  "127.0.0.1";
     *  "127.0.0.2";
     * }
     *
     * @return mixed|null|string
     * @deprecated 100.2.0
     * @see \Magento\PageCache\Model\VclGeneratorInterface::generateVcl
     */
    protected function _getAccessList()
    {
        $tpl = '    "%s";';
        $accessList = $this->_scopeConfig->getValue(self::XML_VARNISH_PAGECACHE_ACCESS_LIST);
        if (!empty($accessList)) {
            $ipsList = [];
            $ips = explode(',', $accessList);
            foreach ($ips as $ip) {
                $ipsList[] = sprintf($tpl, trim($ip));
            }
            return implode("\n", $ipsList);
        }

        return '';
    }

    /**
     * Get regexs for design exceptions
     *
     * Different browser user-agents may use different themes
     * Varnish supports regex with internal modifiers only so
     * we have to convert "/pattern/iU" into "(?Ui)pattern"
     *
     * @return string
     * @deprecated 100.2.0
     * @see \Magento\PageCache\Model\VclGeneratorInterface::generateVcl
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
            $rules = array_values($this->serializer->unserialize($expressions));
            foreach ($rules as $i => $rule) {
                if (preg_match('/^[\W]{1}(.*)[\W]{1}(\w+)?$/', $rule['regexp'] ?? '', $matches)) {
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
     */
    public function isEnabled()
    {
        return $this->_cacheState->isEnabled(Type::TYPE_IDENTIFIER);
    }
}
