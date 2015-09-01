<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

class CustomerData extends \Magento\Framework\View\Element\Template
{
    /**
     * Sections that can not be cached on frontend-side
     *
     * @var array
     */
    protected $nonCachedSections = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param array $nonCachedSections
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        array $nonCachedSections = []
    ) {
        $this->nonCachedSections = $nonCachedSections;
        parent::__construct($context, $data);
    }

    /**
     * Get CookieLifeTime
     * @return null|string scopeCode
     */
    public function getCookieLifeTime()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get url for customer data ajax requests. Returns url with protocol matching used to request page.
     *
     * @param string $route
     * @return string Customer data url.
     */
    public function getCustomerDataUrl($route)
    {
        return $this->getUrl($route, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get sections that can not be cached on frontend-side
     *
     * @return array
     */
    public function getNotCachedSections()
    {
        return $this->nonCachedSections;
    }

    /**
     * Get keys of sections that can not be cached on frontend-side
     *
     * @return array
     */
    public function getNonCachedSectionKeys()
    {
        return array_keys($this->nonCachedSections);
    }
}
