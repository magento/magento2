<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Di\Definition\Serializer;

class Standard implements SerializerInterface
{
    /**
     * Serialize input data
     *
     * @param mixed $data
     * @return string
     */
    public function serialize($data)
    {
        return serialize($data);
    }
}
