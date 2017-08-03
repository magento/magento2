<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverPool;

/**
 * Scope information block
 *
 * @api
 * @since 2.1.0
 */
class Scope extends Template
{
    /**
     * @var ScopeResolverPool
     * @since 2.1.0
     */
    private $scopeResolverPool;

    /**
     * @param Context $context
     * @param ScopeResolverPool $scopeResolverPool
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        ScopeResolverPool $scopeResolverPool
    ) {
        parent::__construct($context);
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * Retrieve scope title
     *
     * @return string
     * @since 2.1.0
     */
    public function getScopeTitle()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scope_id');

        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            $scopeObject = $scopeResolver->getScope($scopeId);
            return __('%1', $scopeObject->getScopeTypeName());
        }

        return __('Default');
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function toHtml()
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            return '';
        }
        return parent::toHtml();
    }
}
