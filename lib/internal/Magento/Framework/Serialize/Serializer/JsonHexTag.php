<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 *
 * @api
 * @since 100.2.0
 */
class JsonHexTag extends Json implements SerializerInterface
{
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function serialize($data): string
    {
        $result = json_encode($data, JSON_HEX_TAG);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }
}
