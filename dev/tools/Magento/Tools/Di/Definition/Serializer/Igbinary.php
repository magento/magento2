<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Di\Definition\Serializer;

class Igbinary implements SerializerInterface
{
    /**
     * Igbinary constructor
     */
    public function __construct()
    {
        if (!function_exists('igbinary_serialize')) {
            throw new \LogicException('Igbinary extension not loaded');
        }
    }

    /**
     * Serialize input data
     *
     * @param mixed $data
     * @return string
     */
    public function serialize($data)
    {
        return igbinary_serialize($data);
    }
}
