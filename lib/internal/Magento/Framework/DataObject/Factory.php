<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

/**
 * Class \Magento\Framework\DataObject\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return new \Magento\Framework\DataObject($data);
    }
}
