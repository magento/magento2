<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Guest;

use Magento\Customer\Model\Context;

/**
 * "Orders and Returns" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return '';
        }
        return parent::_toHtml();
    }
}
