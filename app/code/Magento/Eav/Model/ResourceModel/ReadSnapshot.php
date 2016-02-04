<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class ReadSnapshot
 */
class ReadSnapshot extends ReadHandler
{
    /**
     * @param string $entityType
     * @param array $data
     * @return array
     */
    protected function getActionContext($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $contextFields = $metadata->getEntityContext();
        $context = [];
        foreach ($contextFields as $field) {
            if ('store_id' == $field && array_key_exists($field, $data) && $data[$field] == 1) {
                $context[$field] = 0;
                continue;
            }
            if (isset($data[$field])) {
                $context[$field] = $data[$field];
            }
        }
        return $context;
    }
}
