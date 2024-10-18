<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Formatter;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Formatter for configurable product options
 */
class Option
{
    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var OptionValue
     */
    private $valueFormatter;

    /**
     * @param Uid $idEncoder
     * @param OptionValue $valueFormatter
     */
    public function __construct(
        Uid $idEncoder,
        OptionValue $valueFormatter
    ) {
        $this->idEncoder = $idEncoder;
        $this->valueFormatter = $valueFormatter;
    }

    /**
     *  Format configurable product options according to the GraphQL schema
     *
     * @param Attribute $attribute
     * @param array $optionIds
     * @return array|null
     */
    public function format(Attribute $attribute, array $optionIds): ?array
    {
        $optionValues = [];

        foreach ($attribute->getOptions() as $option) {
            $optionValues[] = $this->valueFormatter->format($option, $attribute, $optionIds);
        }

        return [
            'uid' => $this->idEncoder->encode($attribute->getProductSuperAttributeId()),
            'attribute_code' => $attribute->getProductAttribute()->getAttributeCode(),
            'label' => $attribute->getLabel(),
            'values' => $optionValues,
        ];
    }
}
