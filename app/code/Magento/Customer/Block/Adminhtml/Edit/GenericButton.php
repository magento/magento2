<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Class GenericButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 * @since 2.0.0
 */
class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Return the customer Id.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     * @since 2.0.0
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
