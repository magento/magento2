<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProductGraphQl\Model\Options\Metadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver class for option selection metadata.
 */
class OptionsSelectionMetadata implements ResolverInterface
{
    /**
     * @var Metadata
     */
    private $configurableSelectionMetadata;

    /**
     * @param Metadata $configurableSelectionMetadata
     */
    public function __construct(
        Metadata $configurableSelectionMetadata
    ) {
        $this->configurableSelectionMetadata = $configurableSelectionMetadata;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $selectedOptions = $args['configurableOptionValueUids'] ?? [];
        /** @var ProductInterface $product */
        $product = $value['model'];

        return $this->configurableSelectionMetadata->getAvailableSelections($product, $selectedOptions);
    }
}
