<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * Map index data to search engine metadata
 * Convert array [[attribute_id => [entity_id => value], ... ]] to applicable for search engine [[attribute => value],]
 */
interface BatchDataMapperInterface
{
    /**
     * Map index data for using in search engine metadata
     *
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = []);
}
