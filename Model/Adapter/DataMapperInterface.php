<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * @deprecated 2.2.0
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface
 * @since 2.1.0
 */
interface DataMapperInterface
{
    /**
     * Prepare index data for using in search engine metadata
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @since 2.1.0
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []);
}
