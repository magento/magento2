<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\PageCache\Model\System\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface,
    Magento\Framework\Option\ArrayInterface;

/**
 * VCL source class
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
     * retrieve options array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $varnishVersions = $this->scopeConfig->getValue(
            \Magento\PageCache\Model\Config::VARNISH_CONFIGURATION_PATH
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
