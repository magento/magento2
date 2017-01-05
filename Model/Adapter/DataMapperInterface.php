<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

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
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []);
}
