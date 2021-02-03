<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Easy\AlgorithmProvider;

class JweContentAlgorithmManagerFactory
{
    private const ALGOS = [
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256::class,
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A192CBCHS384::class,
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512::class,
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A128GCM::class,
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A192GCM::class,
        \Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM::class,
    ];

    public function create(): AlgorithmManager
    {
        return new AlgorithmManager((new AlgorithmProvider(self::ALGOS))->getAvailableAlgorithms());
    }
}
