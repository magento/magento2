<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

interface IdentityResolverInterface
{

    /**
     * Get identifiers from resolved data
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData) : array;
}
