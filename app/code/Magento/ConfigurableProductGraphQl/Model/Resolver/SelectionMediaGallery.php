<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
        if (!isset($value['model']) || !$value['model']) {
            return null;
        }

        $product = $value['model'];
        $availableSelectionProducts = $value['availableSelectionProducts'];
        $mediaGalleryEntries = [];
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($usedProducts as $usedProduct) {
            if (in_array($usedProduct->getId(), $availableSelectionProducts)) {
                foreach ($usedProduct->getMediaGalleryEntries() ?? [] as $key => $entry) {
                    $entryData = $entry->getData();
                    $initialIndex = $usedProduct->getId() . '_' . $key;
                    $index = $this->prepareIndex($entryData, $initialIndex);
                    $mediaGalleryEntries[$index] = $entryData;
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

    /**
     * Formulate an index to have unique set of media entries
     *
     * @param array $entryData
     * @param string $initialIndex
     * @return string
     */
    private function prepareIndex(array $entryData, string $initialIndex) : string
    {
        $index = $initialIndex;
        if (isset($entryData['media_type'])) {
            $index = $entryData['media_type'];
        }
        if (isset($entryData['file'])) {
            $index = $index.'_'.$entryData['file'];
        }
        if (isset($entryData['position'])) {
            $index = $index.'_'.$entryData['position'];
        }
        return $index;
    }
}
