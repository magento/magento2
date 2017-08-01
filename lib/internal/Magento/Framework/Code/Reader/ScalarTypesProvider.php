<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Reader;

/**
 * Class ScalarTypesProvider returns array of supported scalar types.
 * @since 2.2.0
 */
class ScalarTypesProvider
{
    /**
     * Return array of scalar types.
     *
     * @return array
     * @since 2.2.0
     */
    public function getTypes()
    {
        return [
            'array',
            'string',
            'int',
            'integer',
            'float',
            'bool',
            'boolean',
            'mixed',
            'callable',
        ];
    }
}
