<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Customer\Model\Context;

/**
 * Html page footer block
 */
class Footer extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Copyright information
     *
     * @var string
     */
    protected $_copyright;

    /**
     * Miscellaneous HTML information
     *
     * @var string
     */
    private $miscellaneousHtml;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * Set footer data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_tags' => [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG],
            ]
        );
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'PAGE_FOOTER',
            $this->_storeManager->getStore()->getId(),
            (int)$this->_storeManager->getStore()->isCurrentlySecure(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_AUTH),
            $this->getTemplateFile(),
            'template' => $this->getTemplate()
        ];
    }

    /**
     * Retrieve copyright information
     *
     * @return string
     */
    public function getCopyright()
    {
        if (!$this->_copyright) {
            $this->_copyright = $this->_scopeConfig->getValue(
                'design/footer/copyright',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return __($this->_copyright);
    }

    /**
     * Retrieve Miscellaneous HTML information
     *
     * @return string
     */
    public function getMiscellaneousHtml()
    {
        if ($this->miscellaneousHtml === null) {
            $this->miscellaneousHtml = $this->_scopeConfig->getValue(
                'design/footer/absolute_footer',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->miscellaneousHtml;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG];
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime() ?: 3600;
    }
}
