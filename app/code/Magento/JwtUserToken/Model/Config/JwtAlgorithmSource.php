<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * JWT Algorithm options
 */
class JwtAlgorithmSource implements OptionSourceInterface
{
    public const ALG_TYPE_JWS = 0;

    public const ALG_TYPE_JWE = 1;

    public function getAlgorithmType(string $alg): int
    {

    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        // TODO: Implement toOptionArray() method.
    }
}
