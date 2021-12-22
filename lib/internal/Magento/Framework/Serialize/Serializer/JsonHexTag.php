<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Serialize data to JSON with the JSON_HEX_TAG option enabled
 * (All < and > are converted to \u003C and \u003E),
 * unserialize JSON encoded data
 *
 * @api
 * @since 102.0.1
 */
class JsonHexTag extends Json implements SerializerInterface
{
    /**
     * @inheritDoc
     * @since 102.0.1
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
