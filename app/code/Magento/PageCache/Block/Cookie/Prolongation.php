<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\Cookie;

use \Magento\PageCache\Model\Config as PageCacheConfig;

/**
 * Cookie prolongation block.
 */
class Prolongation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $this->_cacheState = $context->getCacheState();
        $this->_scopeConfig = $context->getScopeConfig();

        parent::__construct($context, $data);
    }

    /**
     * Returns encoded script options.
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'prolongActionUrl' => $this->getUrl(
                'page_cache/cookie/prolong',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            ),
        ];

        return json_encode($params);
    }

    /**
     * Determines whether cookie prolongation is allowed.
     * NOTE: cookie prolongation request is performed when varnish is used as a caching application.
     *
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            && $this->_scopeConfig->getValue(PageCacheConfig::XML_PAGECACHE_TYPE) == PageCacheConfig::VARNISH;
    }
}