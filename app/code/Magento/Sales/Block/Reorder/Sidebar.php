<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Reorder;

use Magento\Customer\Model\Context;

/**
 * Last ordered items sidebar
 *
 * @api
 * @since 2.0.0
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('checkout/cart/addgroup', ['_secure' => true]);
    }

    /**
     * Render "My Orders" sidebar block
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $isValid = $this->httpContext->getValue(Context::CONTEXT_AUTH) || $this->getCustomerId();
        return $isValid ? parent::_toHtml() : '';
    }
}
