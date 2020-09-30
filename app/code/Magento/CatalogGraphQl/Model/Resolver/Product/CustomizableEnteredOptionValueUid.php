<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Format new option uid in base64 encode for entered custom options
 */
class CustomizableEnteredOptionValueUid implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'custom-option';

    /**
     * Create a option uid for entered option in "<option-type>/<option-id>" format
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return string
     *
     * @throws GraphQlInputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($value['uid'])) {
            return $value['uid'];
        }
        if (!isset($value['option_id']) || empty($value['option_id'])) {
            throw new GraphQlInputException(__('"option_id" value should be specified.'));
        }

        $optionDetails = [
            self::OPTION_TYPE,
            $value['option_id']
        ];

        $content = implode('/', $optionDetails);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_encode($content);
    }
}
