<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Validator\UrlKey;

/**
 * Interface UrlKeyValidatorInterface is responsive for validating urlKeys
 */
interface UrlKeyValidatorInterface
{
    /**
     * Validates urlKey
     *
     * @param string $urlKey
     * @return array
     */
    public function validate(string $urlKey): array;
}
