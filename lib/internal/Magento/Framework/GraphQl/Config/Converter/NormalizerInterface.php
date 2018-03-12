<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\Converter;

/**
 * Normalize XML structured GraphQL data that was converted to an array.
 */
interface NormalizerInterface
{
    /**
     * Normalize XML formatted array to a format readable by GraphQL element processing.
     *
     * @param $source
     * @return array
     */
    public function normalize(array $source) : array;
}
