<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bulk\Api;

/**
 * Interface UuidInterface
 */
interface UuidInterface
{
    /**
     * @param string $entityTypePrefix Entity Type Prefix
     * @return string ID
     **/
    public function generateId($entityTypePrefix);
    
    /**
     * @param string $entityTypePrefix Entity Type Prefix
     * @param string $data
     * @return string ID
     **/
    public function generateIdForData($entityTypePrefix, $data);
}
