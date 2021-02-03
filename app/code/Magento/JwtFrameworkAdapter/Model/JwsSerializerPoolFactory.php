<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer;
use Jose\Component\Signature\Serializer\JSONGeneralSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;

class JwsSerializerPoolFactory
{
    public function create(): JWSSerializerManager
    {
        return new JWSSerializerManager(
            [
                new CompactSerializer(),
                new JSONGeneralSerializer(),
                new JSONFlattenedSerializer()
            ]
        );
    }
}
