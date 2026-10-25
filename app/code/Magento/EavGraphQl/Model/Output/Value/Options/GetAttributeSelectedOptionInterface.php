<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value\Options;

/**
 * Interface for getting custom attributes seelcted options.
 */
interface GetAttributeSelectedOptionInterface
{
    /**
     * Retrieve all selected options of an attribute filtered by attribute code
     *
     * @param string $entity
     * @param string $code
     * @param string $value
     * @return array|null
     */
    public function execute(string $entity, string $code, string $value): ?array;
}
