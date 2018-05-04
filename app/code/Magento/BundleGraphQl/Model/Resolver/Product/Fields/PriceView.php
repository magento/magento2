<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\BundleGraphQl\Model\Resolver\Product\Fields;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * {@inheritdoc}
 */
class PriceView implements ResolverInterface
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param EnumLookup $enumLookup
     * @param ValueFactory $valueFactory
     */
    public function __construct(EnumLookup $enumLookup, ValueFactory $valueFactory)
    {
        $this->enumLookup = $enumLookup;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $result = function () {
            return null;
        };
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['price_view'])
                ? $this->enumLookup->getEnumValueFromField('PriceViewEnum', $value['price_view']) : null;
        }

        return $this->valueFactory->create(
            function () use ($result) {
                return $result;
            }
        );
    }
}
