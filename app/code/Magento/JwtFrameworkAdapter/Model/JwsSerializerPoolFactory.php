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
use Jose\Component\Signature\Serializer\JWSSerializerManagerFactory;

class JwsSerializerPoolFactory
{
    public function create(): JWSSerializerManagerFactory
    {
        $jwsSerializerFactory = new JWSSerializerManagerFactory();
        $jwsSerializerFactory->add(new CompactSerializer());
        $jwsSerializerFactory->add(new JSONGeneralSerializer());
        $jwsSerializerFactory->add(new JSONFlattenedSerializer());

        return $jwsSerializerFactory;
    }
}
