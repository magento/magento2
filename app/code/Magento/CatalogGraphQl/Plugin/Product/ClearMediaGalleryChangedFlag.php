<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\Product;

use Magento\Catalog\Model\Product;

class ClearMediaGalleryChangedFlag
{
    /**
     * Save information about associate Pickup Location Code to Quote Address.
     *
     * @param Product $subject
     * @param Product $result
     *
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(Product $subject, Product $result): Product
    {
        $result->unsetData('is_media_gallery_changed');
        return $result;
    }

    /**
     * Check if address can have a Pickup Location.
     *
     * @param Address $address
     *
     * @return bool
     */
    private function validateAddress(Address $address): bool
    {
        return $address->getExtensionAttributes() && $address->getAddressType() === Address::ADDRESS_TYPE_SHIPPING;
    }
}
