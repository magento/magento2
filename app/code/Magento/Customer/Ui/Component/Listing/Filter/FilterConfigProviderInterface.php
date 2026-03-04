<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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
