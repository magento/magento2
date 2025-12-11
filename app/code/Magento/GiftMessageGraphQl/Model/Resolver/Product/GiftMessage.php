<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as Virtual;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessageGraphQl\Model\Config\Messages;

class GiftMessage implements ResolverInterface
{
    /**
     * @param Messages $messagesConfig
     */
    public function __construct(
        private readonly Messages $messagesConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): bool {
        if (!isset($value['model']) || !$value['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('The product model is not available.'));
        }

        if (in_array($value['model']['type_id'], [Virtual::TYPE_VIRTUAL, Downloadable::TYPE_DOWNLOADABLE], true)) {
            return false;
        }

        return $this->messagesConfig->isGiftMessageAllowedForProduct(
            $value['model']->getGiftMessageAvailable(),
            $context->getExtensionAttributes()->getStore()
        );
    }
}
