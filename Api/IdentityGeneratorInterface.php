<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api;

use Magento\Framework\Bulk\Api\Data\UuidInterface;
/**
 * Interface UuidInterface
 */
interface IdentityGeneratorInterface
{
    /**
     * @return UuidInterface
     **/
    public function generateId();
    
    /**
     * @param string $data
     * @return UuidInterface
     **/
    public function generateIdForData($data);
}
