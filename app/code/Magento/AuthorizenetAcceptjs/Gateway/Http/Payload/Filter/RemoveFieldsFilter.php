<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Filter;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;

/**
 * Removes a set of fields from the payload
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RemoveFieldsFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $fields;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    public function filter(array $data): array
    {
        foreach ($this->fields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
