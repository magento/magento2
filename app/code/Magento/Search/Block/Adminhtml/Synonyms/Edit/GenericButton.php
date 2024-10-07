<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Synonyms\Edit;

use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Search\Controller\RegistryConstants;

/**
 * Class GenericButton
 */
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
     * Return the synonyms group Id.
     *
     * @return int|null
     */
    public function getGroupId()
    {
        $synGroup = $this->registry->registry(RegistryConstants::SEARCH_SYNONYMS);
        return $synGroup ? $synGroup->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
