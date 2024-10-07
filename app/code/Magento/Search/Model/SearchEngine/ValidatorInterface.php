<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Model\SearchEngine;

/**
 * Validate search engine configuration
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate search engine
     *
     * @return string[] array of errors, empty array if validation passed
     */
    public function validate(): array;
}
