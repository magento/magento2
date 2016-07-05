<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api;

use Magento\Framework\Bulk\Api\Data\IdentityInterface;
/**
 * Interface UuidInterface
 */
interface IdentityGeneratorInterface
{
    /**
     * @return string
     **/
    public function generateId();
    
    /**
     * @param string $data
     * @return string
     **/
    public function generateIdForData($data);
}
