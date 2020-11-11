<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver class for media gallery of child products.
 */
class SelectionMediaGallery implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['product']) || !$value['product']) {
            return null;
        }

        $product = $value['product'];
        $availableSelectionProducts = $value['availableSelectionProducts'];
        $mediaGalleryEntries = [];
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($usedProducts as $usedProduct) {
            if (in_array($usedProduct->getId(), $availableSelectionProducts)) {
                foreach ($usedProduct->getMediaGalleryEntries() ?? [] as $key => $entry) {
                    $index = $usedProduct->getId() . '_' . $key;
                    $mediaGalleryEntries[$index] = $entry->getData();
                    $mediaGalleryEntries[$index]['model'] = $usedProduct;
                    if ($entry->getExtensionAttributes() && $entry->getExtensionAttributes()->getVideoContent()) {
                        $mediaGalleryEntries[$index]['video_content']
                            = $entry->getExtensionAttributes()->getVideoContent()->getData();
                    }
                }
            }
        }
        return $mediaGalleryEntries;
    }
}
