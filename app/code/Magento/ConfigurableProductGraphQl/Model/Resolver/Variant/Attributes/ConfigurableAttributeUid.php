<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant\Attributes;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Format new option uid in base64 encode for super attribute options
 */
class ConfigurableAttributeUid implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'configurable';

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param Uid $uidEncoder
     */
    public function __construct(Uid $uidEncoder)
    {
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Create a option uid for super attribute in "<option-type>/<attribute-id>/<value-index>" format
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
        if (!isset($value['attribute_id']) || empty($value['attribute_id'])) {
            throw new GraphQlInputException(__('"attribute_id" value should be specified.'));
        }

        if (!isset($value['value_index']) || empty($value['value_index'])) {
            throw new GraphQlInputException(__('"value_index" value should be specified.'));
        }

        $optionDetails = [
            self::OPTION_TYPE,
            $value['attribute_id'],
            $value['value_index']
        ];

        $content = implode('/', $optionDetails);

        return $this->uidEncoder->encode($content);
    }
}
