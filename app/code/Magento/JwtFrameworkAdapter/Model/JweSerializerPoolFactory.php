<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JSONFlattenedSerializer;
use Jose\Component\Encryption\Serializer\JSONGeneralSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;

class JweSerializerPoolFactory
{
    public function create(): JWESerializerManager
    {
        return new JWESerializerManager(
            [
                new CompactSerializer(),
                new JSONGeneralSerializer(),
                new JSONFlattenedSerializer()
            ]
        );
    }
}
