<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Block\Adminhtml\Edit;

use Magento\CatalogRule\Controller\RegistryConstants;

/**
 * Class \Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton
 *
 * @since 2.1.0
 */
class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.1.0
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Return the current Catalog Rule Id.
     *
     * @return int|null
     * @since 2.1.0
     */
    public function getRuleId()
    {
        $catalogRule = $this->registry->registry(RegistryConstants::CURRENT_CATALOG_RULE_ID);
        return $catalogRule ? $catalogRule->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function canRender($name)
    {
        return $name;
    }
}
