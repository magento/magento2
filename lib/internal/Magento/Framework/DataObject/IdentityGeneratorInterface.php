<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

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
