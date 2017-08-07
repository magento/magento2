<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

/**
 * Interface UuidInterface
 * @since 2.2.0
 */
interface IdentityGeneratorInterface
{
    /**
     * Generate id
     *
     * @return string
     * @since 2.2.0
     **/
    public function generateId();
    
    /**
     * Generate id for data
     *
     * @param string $data
     * @return string
     * @since 2.2.0
     **/
    public function generateIdForData($data);
}
