<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Options;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Format new option uid in base64 encode for entered bundle options
 */
class BundleItemOptionUid implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'bundle';

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
     * Create a option uid for entered option in "<option-type>/<option-id>/<option-value-id>/<quantity>" format
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
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['option_id']) || empty($value['option_id'])) {
            throw new GraphQlInputException(__('"option_id" value should be specified.'));
        }

        if (!isset($value['selection_id']) || empty($value['selection_id'])) {
            throw new GraphQlInputException(__('"selection_id" value should be specified.'));
        }

        $optionDetails = [
            self::OPTION_TYPE,
            $value['option_id'],
            $value['selection_id'],
            (int) $value['selection_qty']
        ];

        $content = implode('/', $optionDetails);

        return $this->uidEncoder->encode($content);
    }
}
