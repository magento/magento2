<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SwaggerWebapiAsync\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;

/**
 * Async swagger schema type.
 */
class Async implements SchemaTypeInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * Async constructor.
     *
     * @param string $code
     */
    public function __construct(string $code = 'async')
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|null $store
     * @return string
     */
    public function getSchemaUrlPath($store = null)
    {
        $store = $store ?? 'all';

        return '/rest/' . $store . '/' . $this->code . '/schema?services=all';
    }
}
