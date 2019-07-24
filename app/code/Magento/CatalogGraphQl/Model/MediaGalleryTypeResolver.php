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
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        // resolve type based on the data
        return '';
    }
}
