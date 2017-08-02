<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Unserialize;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class Unserialize
{
    /**
     * Serializer for safe string unserialization.
     *
     * @var Serialize
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param Serialize|null $serializer Optional parameter for backward compatibility.
     * @since 2.2.0
     */
    public function __construct(Serialize $serializer = null)
    {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Serialize::class);
    }

    /**
     * @param string $string
     * @return bool|mixed
     * @since 2.0.0
     */
    public function unserialize($string)
    {
        if (preg_match('/[oc]:[+\-]?\d+:"/i', $string)) {
            trigger_error('String contains serialized object');
            return false;
        }
        return $this->serializer->unserialize($string);
    }
}
