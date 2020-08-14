<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Wishlist;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the selected configurable options
 */
class ConfigurableOptions implements ResolverInterface
{
    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * @param Configuration $configurationHelper
     */
    public function __construct(
        Configuration $configurationHelper
    ) {
        $this->configurationHelper = $configurationHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }

        /** @var ItemInterface $item */
        $item = $value['itemModel'];
        $result = [];

        foreach ($this->configurationHelper->getOptions($item) as $option) {
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
