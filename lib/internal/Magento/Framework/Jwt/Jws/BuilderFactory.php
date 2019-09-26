<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;

/**
 * Creates JWS builder.
 */
class BuilderFactory
{
    /**
     * Creates JWS builder with provided algorithm manager.
     *
     * @param AlgorithmManager $algorithmManager
     * @return JWSBuilder
     */
    public function create(AlgorithmManager $algorithmManager): JWSBuilder
    {
        return new JWSBuilder($algorithmManager);
    }
}
