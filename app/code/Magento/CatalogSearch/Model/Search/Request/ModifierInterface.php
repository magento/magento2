<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\Request;

/**
 * Search requests configuration modifier interface
 */
interface ModifierInterface
{
    /**
     * Modifies search requests configuration
     *
     * @param array $requests
     * @return array
     */
    public function modify(array $requests): array;
}
