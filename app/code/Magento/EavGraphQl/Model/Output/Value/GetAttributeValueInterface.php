<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value;

/**
 * Interface for getting custom attributes.
 */
interface GetAttributeValueInterface
{
    /**
     * Retrieve all attributes filtered by attribute code
     *
     * @param string $entity
     * @param string $code
     * @param string $value
     * @return array|null
     */
    public function execute(string $entity, string $code, string $value): ?array;
}
