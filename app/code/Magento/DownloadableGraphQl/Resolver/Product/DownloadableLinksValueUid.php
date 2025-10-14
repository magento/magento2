<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Formatting the uid for downloadable link
 */
class DownloadableLinksValueUid implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'downloadable';

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['id']) || empty($value['id'])) {
            throw new GraphQlInputException(__('"id" value should be specified.'));
        }

        $optionDetails = [
            self::OPTION_TYPE,
            $value['id']
        ];

        $content = implode('/', $optionDetails);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_encode($content);
    }
}
