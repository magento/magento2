<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Attribute;

/**
 * This plugin is responsible for processing of search_weight property of a product attribute,
 * which is used to boost matches by specific attributes.
 *
 * This is part of search accuracy customization functionality.
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class SearchWeight
{
    /**
     * @param \Magento\Framework\Search\Request\Config $config
     */
    public function __construct(
        \Magento\Framework\Search\Request\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Cleans a cache of search requests when attribute's search weight is changed.
     *
     * A product attribute in Magento contains a property named 'search_weight'.
     * This property should be passed to a search adapter.
     * And container which is responsible for this is the Search Request.
     *
     * However, search requests are dynamically generated and therefore cached in the Configuration cache.
     *
     * But, as they're cached, there is a problem when search weight is changed for an attribute
     *   as it will not change in the cache.
     *
     * This plugin solves this issue by resetting cache of search requests
     *   when an attribute's search weight is changed.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     * @return \Magento\Catalog\Model\ResourceModel\Attribute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $isNew = $attribute->isObjectNew();
        $isWeightChanged = $attribute->dataHasChangedFor('search_weight');

        $result = $proceed($attribute);
        if ($isNew || $isWeightChanged) {
            $this->config->reset();
        }

        return $result;
    }
}
