<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for getting custom attributes.
 */
interface GetAttributeValueInterface
{
    /**
     * Retrieve all attributes filtered by attribute code
     *
     * @param string $entityType
     * @param array $customAttribute
     * @return array|null
     * @throws LocalizedException
     */
    public function execute(string $entityType, array $customAttribute): ?array;
}
