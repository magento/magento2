<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Model\Context;
use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Customer authorization link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class AuthorizationLink extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     * @since 2.0.0
     */
    protected $_postDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_customerUrl = $customerUrl;
        $this->_postDataHelper = $postDataHelper;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->isLoggedIn()
            ? $this->_customerUrl->getLogoutUrl()
            : $this->_customerUrl->getLoginUrl();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->isLoggedIn() ? __('Sign Out') : __('Sign In');
    }

    /**
     * Retrieve params for post request
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostParams()
    {
        return $this->_postDataHelper->getPostData($this->getHref());
    }

    /**
     * Is logged in
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
