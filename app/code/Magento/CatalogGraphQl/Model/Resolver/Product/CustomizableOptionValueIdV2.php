<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 *
 * Format new option id_v2 in base64 encode for custom options
 */
class CustomizableOptionValueIdV2 implements ResolverInterface
{
    private const OPTION_TYPE = 'custom-option';

    /**
     * @inheritdoc
     *
     * Create new option id_v2 that encodes details for each option and in most cases can be presented
     * as base64("<option-type>/<option-id>/<option-value-id>")
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $optionDetails = [
            self::OPTION_TYPE,
            $value['option_id'],
            $value['option_type_id']
        ];

        if (!isset($value['option_id']) || empty($value['option_id'])) {
            throw new LocalizedException(__('Wrong format option data: option_id should not be empty.'));
        }

        if (!isset($value['option_type_id']) || empty($value['option_type_id'])) {
            throw new LocalizedException(__('Wrong format option data: option_type_id should not be empty.'));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $content = \implode('/', $optionDetails);

        return base64_encode($content);
    }
}
