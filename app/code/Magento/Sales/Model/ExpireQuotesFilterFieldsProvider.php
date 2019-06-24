<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

/**
 * Provides expire quotes filter fields.
 */
class ExpireQuotesFilterFieldsProvider
{
    /**
     * @var array
     */
    private $expireQuotesFilterFields;

    /**
     * @param array $expireQuotesFilterFields
     */
    public function __construct(
        array $expireQuotesFilterFields = []
    ) {
        $this->expireQuotesFilterFields = $expireQuotesFilterFields;
    }

    /**
     * Get expire quotes filter fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->expireQuotesFilterFields;
    }
}
