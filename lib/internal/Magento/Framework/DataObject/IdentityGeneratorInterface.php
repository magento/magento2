<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

/**
 * Interface UuidInterface
 */
interface IdentityGeneratorInterface
{
    /**
     * Generate id
     *
     * @return string
     **/
    public function generateId();
    
    /**
     * Generate id for data
     *
     * @param string $data
     * @return string
     **/
    public function generateIdForData($data);
}
