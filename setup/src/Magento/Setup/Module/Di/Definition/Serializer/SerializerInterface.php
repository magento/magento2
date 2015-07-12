<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Definition\Serializer;

interface SerializerInterface
{
    /**
     * Serialize input data
     *
     * @param mixed $data
     * @return string
     */
    public function serialize($data);
}
