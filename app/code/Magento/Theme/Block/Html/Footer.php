<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Cms\Model\Block;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Html page footer block
 *
 * @api
 * @since 100.0.2
 */
class Footer extends Template implements IdentityInterface
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
     * @param TemplateContext $context
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        protected readonly HttpContext $httpContext,
        array $data = []
    ) {
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
                'cache_tags' => [Store::CACHE_TAG, Block::CACHE_TAG],
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
                ScopeInterface::SCOPE_STORE
            );
        }
        return __($this->_copyright);
    }

    /**
     * Retrieve Miscellaneous HTML information
     *
     * @return string
     * @since 100.1.0
     */
    public function getMiscellaneousHtml()
    {
        if ($this->miscellaneousHtml === null) {
            $this->miscellaneousHtml = $this->_scopeConfig->getValue(
                'design/footer/absolute_footer',
                ScopeInterface::SCOPE_STORE
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
        return [Store::CACHE_TAG, Block::CACHE_TAG];
    }

    /**
     * Get block cache life time
     *
     * @return int
     * @since 100.2.4
     */
    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime() ?: 3600;
    }
}
