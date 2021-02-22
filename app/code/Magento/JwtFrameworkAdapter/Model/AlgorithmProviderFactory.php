<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Easy\AlgorithmProvider;

class AlgorithmProviderFactory
{
    /**
     * Create provider instance.
     *
     * @param string[] $algorithms Algorithm classes.
     * @return AlgorithmProvider
     */
    public function create(array $algorithms): AlgorithmProvider
    {
        return new AlgorithmProvider($algorithms);
    }
}
