<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SwaggerWebapi\Model\SchemaType;

use Magento\Swagger\Api\Data\SchemaTypeInterface;

/**
 * Rest swagger schema type.
 */
class Rest implements SchemaTypeInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * Rest constructor.
     *
     * @param string $code
     */
    public function __construct(string $code = 'rest')
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

        return '/' . $this->code . '/' . $store . '/schema?services=all';
    }
}
