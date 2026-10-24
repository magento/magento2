<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdvancedSearch\Model\Adapter\DataMapper;

/**
 * Provide additional fields for data mapper during search indexer
 * Must return array with the following format: [[product id] => [field name1 => value1, ...], ...]
 * @api
 * @since 100.2.0
 */
interface AdditionalFieldsProviderInterface
{
    /**
     * Get additional fields for data mapper during search indexer based on product ids and store id.
     *
     * @param array $productIds
     * @param int $storeId
     * @return array
     * @since 100.2.0
     */
    public function getFields(array $productIds, $storeId);
}
