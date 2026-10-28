<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
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
