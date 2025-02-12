<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\DataObject;

/**
 * Class Factory
 *
 * @api
 */
class Factory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function create(array $data = [])
    {
        return new \Magento\Framework\DataObject($data);
    }
}
