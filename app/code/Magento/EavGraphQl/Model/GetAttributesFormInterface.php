<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for getting form attributes metadata.
 */
interface GetAttributesFormInterface
{
    /**
     * Retrieve all attributes filtered by form code
     *
     * @param string $formCode
     * @throws LocalizedException
     */
    public function execute(string $formCode): ?array;
}
