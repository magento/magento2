<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for getting custom attributes selected options.
 */
interface GetAttributeSelectedOptionInterface
{
    /**
     * Retrieve all selected options of an attribute filtered by attribute code
     *
     * @param string $entityType
     * @param array $customAttribute
     * @return array|null
     * @throws LocalizedException
     */
    public function execute(string $entityType, array $customAttribute): ?array;
}
