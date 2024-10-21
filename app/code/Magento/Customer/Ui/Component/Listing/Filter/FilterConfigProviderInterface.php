<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Filter;

interface FilterConfigProviderInterface
{
    /**
     * Returns filter configuration for given attribute
     *
     * @param array $attributeData
     * @return array
     */
    public function getConfig(array $attributeData): array;
}
