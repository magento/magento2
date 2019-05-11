<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class ConfigurableCartItemOptions implements ResolverInterface
{
    private $configurationHelper;

    public function __construct(
        Configuration $configurationHelper
    ) {
        $this->configurationHelper = $configurationHelper;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        $result = [];
        foreach ($this->configurationHelper->getOptions($cartItem) as $option) {
            $result[] = [
                'id' => $option['option_id'],
                'option_label' => $option['label'],
                'value_id' => $option['option_value'],
                'value_label' => $option['value'],
            ];
        }

        return $result;
    }
}
