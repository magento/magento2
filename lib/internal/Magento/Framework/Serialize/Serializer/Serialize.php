<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Less secure than Json implementation, but gives higher performance on big arrays. Does not unserialize objects.
 * Using this implementation is discouraged as it may lead to security vulnerabilities
 */
class Serialize implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize($data)
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        // We have to use serialize
        // phpcs:ignore Magento2.Security.InsecureFunction
        return serialize($data);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($string)
    {
        if (false === $string || null === $string || '' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        set_error_handler(
            function () {
                restore_error_handler();
                throw new \InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            }
        );
        // We have to use unserialize here
        // phpcs:ignore Magento2.Security.InsecureFunction
        $result = unserialize($string, ['allowed_classes' => false]);
        restore_error_handler();
        return $result;
    }
}
