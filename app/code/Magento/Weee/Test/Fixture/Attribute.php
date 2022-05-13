<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Fixture;

use Magento\Framework\DataObject;

class Attribute extends \Magento\Catalog\Test\Fixture\Attribute
{
    private const DEFAULT_DATA = [
        'frontend_input' => 'weee',
    ];

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply(array_merge(self::DEFAULT_DATA, $data));
    }
}
