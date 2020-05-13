<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant\Attributes;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 *
 * Format new option id_v2 in base64 encode for super attribute options
 */
class ConfigurableAttributeIdV2 implements ResolverInterface
{
    private const OPTION_TYPE = 'configurable';

    /**
     * @var Attribute
     */
    private $eavAttribute;

    /**
     * ConfigurableAttributeIdV2 constructor.
     *
     * @param Attribute $eavAttribute
     */
    public function __construct(Attribute $eavAttribute)
    {
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @inheritdoc
     *
     * Create new option id_v2 that encodes details for each option and in most cases can be presented
     * as base64("<option-type>/<attribute-id>/<value-index>")
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|string
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $attribute_id = $this->eavAttribute->getIdByCode('catalog_product', $value['code']);
        $optionDetails = [
            self::OPTION_TYPE,
            $attribute_id,
            $value['value_index']
        ];

        if (empty($attribute_id)) {
            throw new LocalizedException(__('Wrong format option data: attribute_id should not be empty.'));
        }

        if (!isset($value['value_index']) || empty($value['value_index'])) {
            throw new LocalizedException(__('Wrong format option data: value_index should not be empty.'));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $content = \implode('/', $optionDetails);

        return base64_encode($content);
    }
}
