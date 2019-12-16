<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolver for Media Gallery type.
 */
class MediaGalleryTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritdoc
     *
     * @param array $data
     * @return string
     */
    public function resolveType(array $data) : string
    {
        // resolve type based on the data
        if (isset($data['media_type']) && $data['media_type'] == 'image') {
            return 'ProductImage';
        }
        if (isset($data['media_type']) && $data['media_type'] == 'external-video') {
            return 'ProductVideo';
        }
    }
}
