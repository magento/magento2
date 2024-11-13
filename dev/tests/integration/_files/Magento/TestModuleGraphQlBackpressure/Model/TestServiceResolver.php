<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleGraphQlBackpressure\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class TestServiceResolver implements ResolverInterface
{
    /**
     * @var int
     */
    private int $counter = 0;

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->counter++;

        return ['str' => 'read'];
    }

    public function resetCounter(): void
    {
        $this->counter = 0;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }
}
