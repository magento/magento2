<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\PageCache\Model\System\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface,
    Magento\Framework\Option\ArrayInterface,
    Magento\PageCache\Model\Config;

/**
 * Varnish versions source class
 * 
 */
class VarnishVersion implements ArrayInterface
{
    /**
     * 
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * 
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * retrieve varnish versions as option array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $varnishVersions = $this->scopeConfig->getValue(
            Config::VARNISH_CONFIGURATION_BASE_PATH
        );
        foreach ($varnishVersions as $versionId => $varnishVersion) {
            if (!isset($varnishVersion['label'], $varnishVersion['vcl'])) {
                continue;
            }
            $options[$versionId] = $varnishVersion['label'];
        }
        return $options;
    }
}
