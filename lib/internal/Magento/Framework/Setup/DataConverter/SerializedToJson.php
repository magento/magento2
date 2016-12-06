<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\DataConverter;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Convert from serialized to JSON format
 */
class SerializedToJson implements DataConverterInterface
{
    /**
     * @var Serialize
     */
    private $serialize;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param Serialize $serialize
     * @param Json $json
     */
    public function __construct(
        Serialize $serialize,
        Json $json
    ) {
        $this->serialize = $serialize;
        $this->json = $json;
    }

    /**
     * Convert from serialized to JSON format
     *
     * @param string $string
     * @return string
     */
    public function convert($string)
    {
        return $this->json->serialize($this->serialize->unserialize($string));
    }
}
