<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Model\RegistryConstants;

class GenericButton
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param WidgetContext $context
     * @param Registry $registry Registry
     */
    public function __construct(
        WidgetContext $context,
        protected readonly Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Return the current sales Rule Id.
     *
     * @return int|null
     */
    public function getRuleId()
    {
        $salesRule = $this->registry->registry(RegistryConstants::CURRENT_SALES_RULE);
        return $salesRule ? $salesRule->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Check where button can be rendered
     *
     * @param string $name
     * @return string
     */
    public function canRender($name)
    {
        return $name;
    }
}
