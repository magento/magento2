<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Wishlist;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the selected configurable options
 */
class ConfigurableOptions implements ResolverInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'configurable';

    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @param Configuration $configurationHelper
     * @param MetadataPool $metadataPool
     * @param Uid $uidEncoder
     */
    public function __construct(
        Configuration $configurationHelper,
        MetadataPool $metadataPool,
        Uid $uidEncoder
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->metadataPool = $metadataPool;
        $this->uidEncoder = $uidEncoder;
    }

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
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }

        /** @var ItemInterface $item */
        $item = $value['itemModel'];
        $result = [];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productLinkId = $item->getProduct()->getData($linkField);

        foreach ($this->configurationHelper->getOptions($item) as $option) {
            if (isset($option['option_type'])) {
                //Don't return customizable options in this resolver
                continue;
            }
            $result[] = [
                'id' => $option['option_id'],
                'configurable_product_option_uid' => $this->uidEncoder->encode(
                    self::OPTION_TYPE . '/' . $productLinkId . '/' . $option['option_id']
                ),
                'option_label' => $option['label'],
                'value_id' => $option['option_value'],
                'configurable_product_option_value_uid' => $this->uidEncoder->encode(
                    self::OPTION_TYPE . '/' . $option['option_id'] . '/' . $option['option_value']
                ),
                'value_label' => $option['value'],
            ];
        }

        return $result;
    }
}
