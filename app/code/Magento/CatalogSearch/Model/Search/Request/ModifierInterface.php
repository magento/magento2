<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
