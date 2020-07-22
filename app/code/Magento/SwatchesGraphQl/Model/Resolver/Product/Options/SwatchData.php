<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Model\Resolver\Product\Options;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider\SwatchDataProvider;

/**
 * Class SwatchData
 *
 * Product swatch data resolver, used for GraphQL request processing
 */
class SwatchData implements ResolverInterface
{
    /**
     * @var SwatchDataProvider
     */
    private $swatchDataProvider;

    /**
     * SwatchData constructor.
     *
     * @param SwatchDataProvider $swatchDataProvider
     */
    public function __construct(
        SwatchDataProvider $swatchDataProvider
    ) {
        $this->swatchDataProvider = $swatchDataProvider;
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
        return $this->swatchDataProvider->getData($value['value_index']);
    }
}
