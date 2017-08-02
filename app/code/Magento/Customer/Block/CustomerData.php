<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

/**
 * @api
 * @since 2.0.0
 */
class CustomerData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $expirableSectionNames;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param array $expirableSectionNames
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        array $expirableSectionNames = []
    ) {
        parent::__construct($context, $data);
        $this->expirableSectionNames = $expirableSectionNames;
    }

    /**
     * Get CookieLifeTime
     * @return null|string scopeCode
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCustomerDataUrl($route)
    {
        return $this->getUrl($route, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Retrieve lifetime period (in minutes) of the frontend section configuration.
     *
     * Once this period has expired the corresponding section must be invalidated and reloaded.
     *
     * @return int section lifetime in minutes
     * @since 2.2.0
     */
    public function getExpirableSectionLifetime()
    {
        return (int)$this->_scopeConfig->getValue('customer/online_customers/section_data_lifetime');
    }

    /**
     * Retrieve the list of sections that can expire.
     *
     * @return array
     * @since 2.2.0
     */
    public function getExpirableSectionNames()
    {
        return array_values($this->expirableSectionNames);
    }
}
